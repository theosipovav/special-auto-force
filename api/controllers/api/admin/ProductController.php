<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\entities\Product;
use app\models\entities\ProductCategory;
use app\models\entities\ProductImage;
use app\models\entities\ImageEntity;
use app\models\dtos\request\CreateProductRequest;
use app\models\dtos\response\Ok201ResponseDto;

/**
 * Админский REST API контроллер товаров (панель администрирования).
 *
 * Все методы требуют авторизации (Bearer JWT) и роли admin / manager.
 *
 * Маршруты:
 *  - GET    /api/admin/products                  — список (CRUD index)
 *  - GET    /api/admin/products/{id}             — просмотр (CRUD view)
 *  - POST   /api/admin/products                  — создание (CRUD create)
 *  - PUT    /api/admin/products/{id}             — обновление (CRUD update)
 *  - DELETE /api/admin/products/{id}             — удаление (CRUD delete)
 *  - POST   /api/admin/products/{id}/sync-categories — привязка категорий
 *  - POST   /api/admin/products/{id}/images      — добавление фото
 */
class ProductController extends BaseApiAdminController
{
    public $modelClass = Product::class;

    /**
     * Настраиваем стандартные CRUD-действия.
     * - index: кастомный DataProvider с фильтрами/сортировкой
     * - view, update: используем стандартные действия родителя
     * - create, delete: переопределены вручную ниже
     */
    public function actions()
    {
        $actions = parent::actions();

        // Переопределяются вручную
        unset($actions['create']);
        unset($actions['delete']);

        

        // Кастомный провайдер для списка
        $actions['index']['prepareDataProvider'] = function () {
            $query = Product::find()->with(['categories', 'productImages.imageEntity']);

            // Фильтр по категории
            $categoryId = Yii::$app->request->get('categoryId');
            if (!empty($categoryId)) {
                $query->joinWith('productCategories')
                    ->andWhere(['{{%product_category}}.category_id' => (int) $categoryId]);
            }

            // Фильтр по наличию
            $inStock = Yii::$app->request->get('inStock');
            if ($inStock !== null && $inStock !== '') {
                $query->andWhere(['in_stock' => ($inStock === '1' || $inStock === 'true' || $inStock === 1) ? 1 : 0]);
            }

            // Поиск
            $search = Yii::$app->request->get('q');
            if (!empty($search)) {
                $query->andFilterWhere([
                    'or',
                    ['like', '{{%product}}.title', $search],
                    ['like', '{{%product}}.article', $search],
                    ['like', '{{%product}}.short_description', $search],
                    ['like', '{{%product}}.long_description', $search],
                    ['like', '{{%product}}.info', $search],
                ]);
            }

            // Сортировка
            $sortParam = Yii::$app->request->get('sort');
            $order = ['id' => SORT_DESC];
            if ($sortParam === 'price_asc') {
                $order = ['price' => SORT_ASC];
            } elseif ($sortParam === 'price_desc') {
                $order = ['price' => SORT_DESC];
            } elseif ($sortParam === 'popular') {
                $order = ['id' => SORT_DESC];
            } elseif ($sortParam === 'title') {
                $order = ['title' => SORT_ASC];
            }

            return new ActiveDataProvider([
                'query' => $query->orderBy($order),
                'pagination' => [
                    'pageSize' => 50,
                    'pageSizeParam' => 'per-page',
                ],
            ]);
        };

        return $actions;
    }


    /**
     * POST /api/admin/products
     * Создание нового товара.
     *
     * @return Ok201ResponseDto
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionCreate()
    {
        $this->checkAccess('create');
        $request = new CreateProductRequest();
        $request->load(Yii::$app->request->getBodyParams(), '');

        if (!$request->validate()) {
            $errorString = json_encode($request->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации CreateProductRequest: ' . $errorString, __METHOD__);
            throw new yii\web\ServerErrorHttpException('Проверьте правильность заполнения данных и повторите запрос');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 1. Создаем товар
            $product = new Product();
            $product->title = $request->title;
            $product->article = $request->article;
            $product->short_description = $request->shortDescription;
            $product->long_description = $request->longDescription;
            $product->info = $request->info;
            $product->price = $request->price;
            $product->in_stock = (int) $request->inStock;
            $product->orders_count = (int) $request->ordersCount;
            $product->manufacturer = $request->manufacturer;
            $product->country = $request->country;

            if (!$product->save()) {
                throw new \Exception('Ошибка сохранения товара: ' . json_encode($product->getErrors()));
            }

            $mainImageUrl = null;

            // 2. Обрабатываем изображения
            foreach ($request->images as $imgDto) {
                if (!$imgDto instanceof \app\models\dtos\request\FormFileImageDto) {
                    continue;
                }

                $imageData = $this->saveImageFromBase64($imgDto->image, $imgDto->title);
                if ($imageData === null) {
                    continue;
                }

                // Создаем запись ImageEntity
                $imageEntity = new ImageEntity();
                $imageEntity->title = $imgDto->title;
                $imageEntity->path = $imageData['path'];
                $imageEntity->url = $imageData['url'];

                if (!$imageEntity->save()) {
                    throw new \Exception('Ошибка сохранения ImageEntity: ' . json_encode($imageEntity->getErrors()));
                }

                // Создаем связь ProductImage
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->image_id = $imageEntity->id;
                $productImage->title = $imgDto->title;
                $productImage->is_main = (bool) $imgDto->isMain;

                if (!$productImage->save()) {
                    throw new \Exception('Ошибка сохранения ProductImage: ' . json_encode($productImage->getErrors()));
                }

                if ($imgDto->isMain) {
                    $mainImageUrl = $imageData['url'];
                }
            }

            // Обновляем главное изображение в Product
            if ($mainImageUrl !== null) {
                $product->main_image = $mainImageUrl;
                $product->save(false);
            }

            // 3. Привязываем категории
            if (!empty($request->categoryIds)) {
                foreach ($request->categoryIds as $catId) {
                    $pc = new ProductCategory();
                    $pc->product_id = $product->id;
                    $pc->category_id = (int) $catId;
                    if (!$pc->save()) {
                        throw new \Exception('Ошибка привязки категории ' . $catId);
                    }
                }
            }

            $transaction->commit();

            return new Ok201ResponseDto("Продукт добавлен", $product->toArray());
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Не удалось создать товар: " . $e->getMessage(), __METHOD__);
            throw new yii\web\ServerErrorHttpException('Не удалось создать товар');
        }
    }

    /**
     * DELETE /api/admin/products/{id}
     * Удаление товара и всех связанных файлов изображений.
     *
     * @param int $id
     * @return null
     * @throws yii\web\NotFoundHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionDelete($id)
    {
        $this->checkAccess('delete');
        $product = Product::findOne($id);
        if (!$product) {
            throw new yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }
        // Удаляем файлы изображений
        $this->deleteImageFiles($product);
        if ($product->delete() === false) {
            Yii::error('Product::delete returned false', __METHOD__);
            throw new yii\web\ServerErrorHttpException('Ошибка при удалении товара.');
        }
        return null;
    }

    /**
     * POST /api/admin/products/{id}/sync-categories
     * Привязка товара к набору категорий.
     *
     * @param int $id
     * @return array
     * @throws yii\web\NotFoundHttpException
     */
    public function actionSyncCategories($id)
    {
        $this->checkAccess('sync-categories');
        $product = Product::findOne($id);
        if (!$product) {
            throw new yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }

        $categoryIds = Yii::$app->request->getBodyParam('categoryIds', []);
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        // Удаляем старые связи
        ProductCategory::deleteAll(['product_id' => $id]);

        // Создаём новые
        foreach ($categoryIds as $catId) {
            $pc = new ProductCategory();
            $pc->product_id = (int) $id;
            $pc->category_id = (int) $catId;
            $pc->save();
        }
        return new Ok201ResponseDto("Категории товара успешно обновлены", $product->toArray());
    }

    /**
     * POST /api/admin/products/{id}/images
     * Добавление фотографии в галерею товара.
     *
     * @param int $id
     * @return array
     * @throws yii\web\NotFoundHttpException
     */
    public function actionAddImage($id)
    {
        $this->checkAccess('add-image');
        $product = Product::findOne($id);
        if (!$product) {
            throw new yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }

        $img = new ProductImage();
        $img->product_id = (int) $id;
        $img->image_id = Yii::$app->request->getBodyParam('image_id');
        $img->title = Yii::$app->request->getBodyParam('title', $product->title);
        $img->is_main = Yii::$app->request->getBodyParam('is_main', false);

        if (!$img->save()) {
            Yii::error("Не удалось сохранить фотографии в галерею товара: " . json_encode($img->getErrors()), __METHOD__);
            throw new \yii\web\ServerErrorHttpException('Не удалось сохранить фотографии в галерею товара');
        }
        return new Ok201ResponseDto("Фотография добавлена в галерею товара", $img->toArray());
    }

    /**
     * Сохранение изображения из Base64 строки на диск.
     *
     * @param string $base64Data
     * @param string $title
     * @return array|null ['path' => ..., 'url' => ...]
     */
    private function saveImageFromBase64($base64Data, $title = 'image')
    {
        if (empty($base64Data)) return null;

        $extension = 'png';
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $extension = $matches[1];
            if ($extension === 'jpeg') $extension = 'jpg';
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        } else {
            $extFromTitle = pathinfo($title, PATHINFO_EXTENSION);
            if (!empty($extFromTitle)) {
                $extension = strtolower($extFromTitle);
            }
        }

        $decoded = base64_decode($base64Data);
        if ($decoded === false) {
            Yii::error('Ошибка декодирования base64', __METHOD__);
            return null;
        }

        $uuid = Yii::$app->security->generateRandomString(32);
        $fileName = $uuid . '.' . $extension;

        $uploadPath = Yii::getAlias('@webroot/uploads/products/');
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                Yii::error('Не удалось создать папку: ' . $uploadPath, __METHOD__);
                return null;
            }
        }

        $fullPath = $uploadPath . $fileName;
        if (file_put_contents($fullPath, $decoded) === false) {
            Yii::error('Не удалось записать файл: ' . $fullPath, __METHOD__);
            return null;
        }
        $relativePath = '/uploads/products/' . $fileName;
        return ['path' => $relativePath, 'url' => $relativePath ];
    }

    /**
     * Удаляет файлы изображений, связанные с товаром.
     */
    private function deleteImageFiles(Product $product)
    {
        $uploadPath = Yii::getAlias('@webroot/uploads/products/');
        foreach ($product->productImages as $image) {
            if (!empty($image->imageEntity)) {
                $this->deleteFile($uploadPath, $image->imageEntity->path);
            }
        }
    }

    /**
     * Удаляет файл по пути.
     */
    private function deleteFile(string $uploadPath, string $filePath)
    {
        $fileName = basename($filePath);
        $fullPath = $uploadPath . $fileName;

        if (file_exists($fullPath) && is_file($fullPath)) {
            if (!unlink($fullPath)) {
                Yii::warning("Не удалось удалить файл: {$fullPath}", __METHOD__);
            }
        } else {
            Yii::info("Файл не найден для удаления: {$fullPath}", __METHOD__);
        }
    }
}
