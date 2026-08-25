<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\Product;
use app\models\entities\ProductCategory;
use app\models\entities\ProductImage;
use app\models\entities\User;
use app\models\dtos\request\CreateProductRequest;
use app\models\dtos\response\OkResponseDto;
use app\models\dtos\response\ErrorResponseDto;

/**
 * REST API контроллер товаров (Product)
 */
class ProductController extends BaseApiController
{
    public $modelClass = Product::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'delete', 'sync-categories', 'add-image'],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Удаляем стандартное действие create, чтобы использовать своё
        unset($actions['create']);
        unset($actions['delete']);

        // Custom filtering in index action
        $actions['index']['prepareDataProvider'] = function () {
            $query = Product::find()->with(['categories', 'productImages']);

            // 1. Filter by category
            $categoryId = Yii::$app->request->get('categoryId');
            if (!empty($categoryId)) {
                $query->joinWith('productCategories')
                    ->andWhere(['{{%product_category}}.category_id' => (int) $categoryId]);
            }

            // 2. Filter by stock status
            $inStock = Yii::$app->request->get('inStock');
            if ($inStock !== null && $inStock !== '') {
                $query->andWhere(['in_stock' => ($inStock === '1' || $inStock === 'true' || $inStock === 1) ? 1 : 0]);
            }

            // 3. Search query
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

            // 4. Sorting
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
                'query' => $query,
                'pagination' => [
                    'pageSize' => 50, // Вывод по ~50 товаров на страницу
                    'pageSizeParam' => 'per-page',
                ],
                'sort' => [
                    'defaultOrder' => $order,
                ],
            ]);
        };

        return $actions;
    }

    /**
     * GET /api/products/latest
     * Карусель: 10 последних добавленных товаров
     */
    public function actionLatest()
    {
        $limit = (int) Yii::$app->request->get('limit', 10);
        $products = Product::find()
            ->with(['categories', 'productImages'])
            ->orderBy(['id' => SORT_DESC])
            ->limit($limit)
            ->all();

        return [
            'success' => true,
            'count' => count($products),
            'items' => $products,
        ];
    }

    /**
     * GET /api/products/popular
     * Карусель: 10 самых популярных товаров (по дате добавления)
     */
    public function actionPopular()
    {
        $limit = (int) Yii::$app->request->get('limit', 10);
        $products = Product::find()
            ->with(['categories', 'productImages'])
            ->orderBy(['id' => SORT_DESC])
            ->limit($limit)
            ->all();

        return [
            'success' => true,
            'count' => count($products),
            'items' => $products,
        ];
    }

    /**
     * GET /api/products/search?q=...
     * Быстрый поиск и автодополнение
     */
    public function actionSearch($q = '')
    {
        if (empty($q)) {
            return ['success' => true, 'total' => 0, 'items' => []];
        }

        $query = Product::find()
            ->with(['categories', 'productImages'])
            ->andFilterWhere([
                'or',
                ['like', 'title', $q],
                ['like', 'article', $q],
                ['like', 'short_description', $q],
                ['like', 'long_description', $q],
            ]);

        $limit = (int) Yii::$app->request->get('limit', 20);
        $products = $query->limit($limit)->all();

        return [
            'success' => true,
            'query' => $q,
            'total' => count($products),
            'items' => $products,
        ];
    }

    /**
     * POST /api/products/{id}/sync-categories
     * Привязка товара к набору категорий
     */
    public function actionSyncCategories($id)
    {
        $product = Product::findOne($id);
        if (!$product) {
            throw new NotFoundHttpException("Товар #{$id} не найден.");
        }

        $categoryIds = Yii::$app->request->getBodyParam('categoryIds', []);
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        // Remove old relations
        ProductCategory::deleteAll(['product_id' => $id]);

        // Insert new relations
        foreach ($categoryIds as $catId) {
            $pc = new ProductCategory();
            $pc->product_id = (int) $id;
            $pc->category_id = (int) $catId;
            $pc->save();
        }

        return [
            'success' => true,
            'message' => 'Категории товара успешно обновлены',
            'product' => $product->toArray(),
        ];
    }

    /**
     * POST /api/products/{id}/images
     * Добавление фотографии в галерею товара
     */
    public function actionAddImage($id)
    {
        $product = Product::findOne($id);
        if (!$product) {
            throw new NotFoundHttpException("Товар #{$id} не найден.");
        }

        $img = new ProductImage();
        $img->product_id = (int) $id;
        $img->image_id = Yii::$app->request->getBodyParam('image_id');
        $img->title = Yii::$app->request->getBodyParam('title', $product->title);
        $img->is_main = Yii::$app->request->getBodyParam('is_main', false);

        if ($img->save()) {
            return [
                'success' => true,
                'message' => 'Фотография добавлена в галерею товара',
                'data' => $img->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'errors' => $img->getErrors(),
        ];
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete', 'sync-categories', 'add-image'])) {

            /** @var User $currentUser */
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser || (!$currentUser->hasRole('admin') && !$currentUser->hasRole('Администратор'))) {
                throw new ForbiddenHttpException('Доступ разрешен только администраторам.');
            }
        }
    }

    /**
     * POST /api/products
     * Создание нового товара.
     *
     * @return OkResponseDto|ErrorResponseDto
     */
    public function actionCreate()
    {
        $request = new CreateProductRequest();
        $request->load(Yii::$app->request->getBodyParams(), '');


        if (!$request->validate()) {
            Yii::$app->response->statusCode = 422;
            return new ErrorResponseDto('Ошибка валидации данных', $request->getErrors());
        }

        $product = $request->createProduct();
        if ($product === null) {
            Yii::$app->response->statusCode = 500;
            return new ErrorResponseDto('Не удалось создать товар', null);
        }

        return new OkResponseDto('Товар успешно создан', $product->toArray());
    }

    /**
     * DELETE /api/products/{id}
     * Удаление товара и всех связанных файлов изображений.
     *
     * @param int $id ID товара
     * @return \yii\web\Response
     * @throws NotFoundHttpException если товар не найден
     * @throws ForbiddenHttpException если недостаточно прав
     */
    public function actionDelete($id)
    {
        $product = Product::findOne($id);
        if (!$product) {
            throw new NotFoundHttpException("Товар #{$id} не найден.");
        }

        // Проверка прав доступа (администратор)
        $this->checkAccess('delete', $product);

        // Удаляем файлы изображений
        $this->deleteImageFiles($product);

        // Удаляем запись товара (каскадно удалятся связанные записи, если настроено)
        if ($product->delete() === false) {
            Yii::$app->response->statusCode = 500;
            return new ErrorResponseDto('Не удалось удалить товар', $product->getErrors());
        }

        Yii::$app->response->statusCode = 204;
        return null;
    }

    /**
     * Удаляет файлы изображений, связанные с товаром.
     *
     * @param Product $product
     */
    private function deleteImageFiles(Product $product)
    {
        $uploadPath = Yii::getAlias('@webroot/web/uploads/products/');

        // Удаляем изображения галереи
        $productImages = $product->productImages;
        foreach ($productImages as $image) {
            if (!empty($image->imageEntity)) {
                $this->deleteFile($uploadPath, $image->imageEntity->path);
            }
        }
    }



    /**
     * Удаляет файл по пути, извлекая имя файла из полного пути.
     *
     * @param string $uploadPath Абсолютный путь к папке загрузок
     * @param string $filePath Путь к файлу (может быть относительным или абсолютным)
     */
    private function deleteFile($uploadPath, $filePath)
    {
        // Извлекаем только имя файла (без пути)
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
