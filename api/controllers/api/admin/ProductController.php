<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\entities\ProductEntity;
use app\models\entities\ProductCategoryEntity;
use app\models\entities\ProductImageEntity;
use app\models\entities\ImageEntity;
use app\models\entities\CustomerRequestEntity;
use app\models\dtos\request\CreateProductRequest;
use app\models\dtos\request\UpdateProductRequest;
use app\models\dtos\response\ProductResponseDto;
use app\models\dtos\response\CategoryResponseDto;
use app\models\dtos\response\ProductImageResponseDto;
use \yii\web\UnprocessableEntityHttpException;
use app\models\dtos\request\FormFileImageDto;
use yii\web\ServerErrorHttpException;

/**
 * Продукция (администратор)
 *
 * @SWG\Tag(
 *     name="admin / product controller",
 *     description="Управление продукцией."
 * )
 */
class ProductController extends BaseApiAdminController
{
    public $modelClass = ProductEntity::class;


    public function actions()
    {
        $actions = parent::actions();

        // Удаляем все, кроме разрешённых views
        $allowed = [];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }

        // Кастомный провайдер для списка
        $actions['index']['prepareDataProvider'] = function () {
            $query = ProductEntity::find()->with(['categories', 'productImages.imageEntity']);

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
     * Создание нового товара.
     *
     * @SWG\Post(
     *     path="/admin/product",
     *     tags={"admin / product controller"},
     *     operationId="adminProductCreate",
     *     summary="Создать товар",
     *     description="Создает новый товар и связанные с ним изображения.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="body", in="body", description="Данные нового товара.", required=true, @SWG\Schema(ref="#/definitions/CreateProductRequest")),
     *     @SWG\Response(response=201, description="Товар успешно создан", @SWG\Schema(ref="#/definitions/ProductResponseDto")),
     *     @SWG\Response(response=422, description="Ошибка валидации данных"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка при создании товара")
     * )
     */
    public function actionCreate()
    {
        $this->checkAccess();
        $request = new CreateProductRequest();
        $request->load(Yii::$app->request->getBodyParams(), '');
        if (!$request->validate()) {
            $errorString = json_encode($request->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации CreateProductRequest: ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Проверьте правильность заполнения данных и повторите запрос');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Создаем продукцию
            $product = new ProductEntity();
            $product->title = $request->title;
            $product->article = $request->article;
            $product->short_description = $request->shortDescription;
            $product->long_description = $request->longDescription;
            $product->info = $request->info;
            $product->price = $request->price;
            $product->in_stock = (int) $request->inStock;
            $product->manufacturer = $request->manufacturer;
            $product->country = $request->country;
            if (!$product->save()) {
                throw new \Exception('Не удалось сохранить продукцию: ' . json_encode($product->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }


            foreach ($request->categoryIds as $key => $categoryId) {
                $pc = new ProductCategoryEntity();
                $pc->product_id = $product->id;
                $pc->category_id = $categoryId;
                if (!$pc->save()) {
                    throw new \Exception('Не удалось привзяать категорию: ' . json_encode($pc->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                }
            }


            // Основное изображение не задно задано
            $isSetMain = false;

            foreach ($request->images as $imgDto) {
                if (!$imgDto instanceof FormFileImageDto) {
                    continue;
                }

                // Создаем изображение
                $image = new ImageEntity();
                $image->saveImageFromBase64($imgDto->base64);
                if (!$image->save()) {
                    throw new \Exception('Не удалось сохранить изображение: ' . json_encode($image->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                }

                // Создаем связь продукции с изображениями
                $productImage = new ProductImageEntity();
                $productImage->product_id = $product->id;
                $productImage->image_id = $image->id;
                $productImage->title = $image->title;
                if (!$isSetMain && $imgDto->isMain) {
                    $productImage->is_main = true;
                    $isSetMain = true;
                } else {
                    $productImage->is_main = false;
                }
                if (!$productImage->save()) {
                    throw new \Exception('Не удалось сохранить связь продукции с изображением: ' . json_encode($productImage->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                }
            }

            $transaction->commit();
            $responseDto = $this->buildProductResponseDto($product);
            Yii::$app->response->statusCode = 201;
            return $responseDto;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Не удалось создать товар: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось создать товар.');
        }
    }

    /**
     * Обновление товара.
     *
     * Обновляются только основные поля товара.
     * Категории и изображения изменяются отдельными endpoint.
     *
     * @SWG\Put(
     *     path="/admin/product/{id}",
     *     tags={"admin / product controller"},
     *     operationId="adminProductUpdate",
     *     summary="Обновить товар",
     *     description="Обновляет основные поля существующего товара. Категории и изображения изменяются отдельными методами.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", description="ID товара", required=true, type="integer", format="int32"),
     *     @SWG\Parameter(name="body", in="body", description="Данные для обновления товара.", required=true, @SWG\Schema(ref="#/definitions/UpdateProductRequest")),
     *     @SWG\Response(response=200, description="Товар успешно обновлен", @SWG\Schema(ref="#/definitions/ProductResponseDto")),
     *     @SWG\Response(response=404, description="Товар не найден"),
     *     @SWG\Response(response=422, description="Ошибка валидации данных"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка при обновлении товара")
     * )
     */
    public function actionUpdate(int $id)
    {
        $this->checkAccess();
        $product = ProductEntity::findOne($id);
        if (!$product) {
            throw new \yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }
        $request = new UpdateProductRequest();
        $request->load(Yii::$app->request->getBodyParams(), '');
        $product->load($request, '');
        if (!$product->save()) {
            $errorString = json_encode($product->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Не удалось обновить продукцию: ' . $errorString, __METHOD__);
            throw new ServerErrorHttpException('Не удалось обновить продукцию: ' . $errorString);
        }

        $responseDto = $this->buildProductResponseDto($product);
        Yii::$app->response->statusCode = 201;
        return $responseDto;
    }


    /**
     * Полная синхронизация изображений товара.
     *
     * Переданный массив images становится новым состоянием галереи.
     * Изображения, отсутствующие в запросе, удаляются.
     *
     *
     * @SWG\Post(
     *     path="/admin/product/{id}/sync-images",
     *     tags={"admin / product controller"},
     *     operationId="adminProductSyncImages",
     *     summary="Синхронизировать изображения товара",
     *     description="Полностью синхронизирует галерею товара. Можно передавать существующие изображения через image_id или новые изображения через base64.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     * 
     *     @SWG\Parameter(name="id", in="path", description="ID товара", required=true, type="integer", format="int32"),
     *     @SWG\Parameter(name="body", in="body", description="Новый состав изображений товара.", required=true, @SWG\Schema(
     *             type="object",
     *             required={"images"},
     *             @SWG\Property(property="images", type="array", description="Массив изображений товара.", @SWG\Items(ref="#/definitions/FormFileImageDto")
     *             )
     *         )
     *     ),
     *     @SWG\Response(response=201, description="Изображения успешно синхронизированы", @SWG\Schema(ref="#/definitions/ProductResponseDto")),
     *     @SWG\Response(response=404, description="Товар не найден"),
     *     @SWG\Response(response=422, description="Некорректный формат данных"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка синхронизации изображений")
     * )
     */
    public function actionSyncImages(int $id)
    {
        $this->checkAccess();

        $product = ProductEntity::findOne($id);
        if (!$product) {
            throw new \yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }

        $formFileImageDtos = Yii::$app->request->getBodyParam('images', []);
        if (!is_array($formFileImageDtos)) {
            throw new UnprocessableEntityHttpException('Поле "images" должно быть массивом.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 1. Получаем существующие связи "товар-изображение"
            $existingProductImages = ProductImageEntity::findAll(['product_id' => $id]);
            $existingMap = [];
            foreach ($existingProductImages as $pi) {
                $existingMap[$pi->image_id] = $pi;
            }

            $requestImageIds = [];
            $isSetMain = false; // Флаг: уже назначили главное изображение

            // 2. Обрабатываем каждое изображение из запроса
            foreach ($formFileImageDtos as $formFileImageDtos) {
                // Преобразуем ассоциативный массив в DTO
                if (is_array($formFileImageDtos)) {
                    $dto = new FormFileImageDto();
                    $dto->load($formFileImageDtos, '');
                } else {
                    $dto = $formFileImageDtos;
                }

                if (!$dto instanceof FormFileImageDto) {
                    throw new \Exception('Неверный формат элемента в массиве images.');
                }
                if (!$dto->validate()) {
                    $errorString = json_encode($dto->getErrors(), JSON_UNESCAPED_UNICODE);
                    throw new \Exception('Ошибка валидации изображения: ' . $errorString);
                }

                // Логика "главного" изображения: только первое с isMain=true становится главным
                $shouldBeMain = !$isSetMain && !empty($dto->isMain);
                if ($shouldBeMain) {
                    $isSetMain = true;
                }

                if (!empty($dto->image_id)) {
                    // --- СУЩЕСТВУЮЩЕЕ ИЗОБРАЖЕНИЕ: обновляем связь ---
                    if (!isset($existingMap[$dto->image_id])) {
                        Yii::warning("image_id={$dto->image_id} не принадлежит товару #{$id}", __METHOD__);
                        continue;
                    }

                    $productImage = $existingMap[$dto->image_id];
                    $productImage->is_main = $shouldBeMain;
                    if (!empty($dto->title)) {
                        $productImage->title = $dto->title;
                    }

                    if (!$productImage->save()) {
                        throw new \Exception('Ошибка обновления ProductImageEntity: ' . json_encode($productImage->getErrors()));
                    }
                    $requestImageIds[] = $dto->image_id;
                } else {
                    // --- НОВОЕ ИЗОБРАЖЕНИЕ: создаём из base64 ---
                    if (empty($dto->base64)) {
                        continue; // Пропускаем пустые записи
                    }

                    $image = new ImageEntity();
                    $image->saveImageFromBase64($dto->base64);
                    if (!$image->save()) {
                        throw new \Exception('Ошибка сохранения ImageEntity: ' . json_encode($image->getErrors()));
                    }

                    $productImage = new ProductImageEntity();
                    $productImage->product_id = $id;
                    $productImage->image_id = $image->id;
                    $productImage->title = !empty($dto->title) ? $dto->title : $image->title;
                    $productImage->is_main = $shouldBeMain;

                    if (!$productImage->save()) {
                        throw new \Exception('Ошибка создания ProductImageEntity: ' . json_encode($productImage->getErrors()));
                    }
                    $requestImageIds[] = $image->id;
                }
            }

            // 3. Удаляем изображения, которых нет в запросе
            foreach ($existingMap as $imageId => $productImage) {
                if (!in_array($imageId, $requestImageIds)) {
                    // Удаляем физический файл
                    if ($productImage->imageEntity) {
                        try {
                            $productImage->imageEntity->DeleteLocalFile();
                        } catch (\Exception $e) {
                            Yii::warning("Не удалось удалить файл изображения: " . $e->getMessage(), __METHOD__);
                        }
                        $productImage->imageEntity->delete();
                    }
                    $productImage->delete();
                }
            }
            $transaction->commit();
            $responseDto = $this->buildProductResponseDto($product);
            Yii::$app->response->statusCode = 201;
            return $responseDto;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Ошибка syncImages для товара #{$id}: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось обновить изображения товара');
        }
    }


    /**
     * Полная синхронизация категорий товара.
     *
     * Все существующие категории товара заменяются переданным массивом.
     *
     * @SWG\Post(
     *     path="/admin/product/{id}/sync-categories",
     *     tags={"admin / product controller"},
     *     operationId="adminProductSyncCategories",
     *     summary="Синхронизировать категории товара",
     *     description="Удаляет существующие связи товара с категориями и создает новые связи на основании переданного массива categoryIds.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", description="ID товара", required=true, type="integer", format="int32"),
     *     @SWG\Parameter(name="body", in="body", description="Идентификаторы категорий.", required=true, @SWG\Schema(
     *          type="object",
     *          required={"categoryIds"},
     *          @SWG\Property(
     *               property="categoryIds",
     *               type="array",
     *               description="Массив идентификаторов категорий.",
     *               @SWG\Items(
     *                   type="integer",
     *                   format="int32"
     *               )
     *           )
     *       )
     *     ),
     *
     *     @SWG\Response(response=201, description="Категории товара успешно синхронизированы", @SWG\Schema(ref="#/definitions/ProductResponseDto")),
     *     @SWG\Response(response=404, description="Товар не найден"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав")
     * )
     */
    public function actionSyncCategories(int $id)
    {
        $this->checkAccess();
        $product = ProductEntity::findOne($id);
        if (!$product) {
            throw new yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }

        $categoryIds = Yii::$app->request->getBodyParam('categoryIds', []);
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        // Удаляем старые связи
        ProductCategoryEntity::deleteAll(['product_id' => $id]);

        // Создаём новые
        foreach ($categoryIds as $catId) {
            $pc = new ProductCategoryEntity();
            $pc->product_id = (int) $id;
            $pc->category_id = (int) $catId;
            $pc->save();
        }
        Yii::$app->response->statusCode = 201;
        return $product->toArray();
    }


    /**
     * Удаление товара.
     *
     * Вместе с товаром удаляются связанные файлы изображений.
     *
     *
     * @SWG\Delete(
     *     path="/admin/product/{id}",
     *     tags={"admin / product controller"},
     *     operationId="adminProductDelete",
     *     summary="Удалить товар",
     *     description="Удаляет товар и связанные с ним файлы изображений.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", description="ID товара", required=true, type="integer", format="int32"),
     *     @SWG\Response(response=204, description="Товар успешно удален"),
     *     @SWG\Response(response=404, description="Товар не найден"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка при удалении товара")
     * )
     */
    public function actionDelete(int $id)
    {
        $this->checkAccess();

        $product = ProductEntity::findOne($id);
        if (!$product) {
            throw new yii\web\NotFoundHttpException("Товар #{$id} не найден.");
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 1. Получаем все записи ProductImageEntity для товара
            $productImages = ProductImageEntity::find()
                ->where(['product_id' => $product->id])
                ->all();

            $imageIds = array_column($productImages, 'image_id');

            // 2. Загружаем все ImageEntity, связанные с этим товаром
            $images = ImageEntity::find()->where(['id' => $imageIds])->all();

            // 3. Удаляем физические файлы изображений
            foreach ($images as $image) {
                try {
                    $image->DeleteLocalFile();
                } catch (\Exception $e) {
                    // Если файл не найден – логируем, но продолжаем удаление записей
                    Yii::warning("Не удалось удалить файл: " . $e->getMessage(), __METHOD__);
                }
            }

            // 4. Удаляем записи ProductImageEntity
            ProductImageEntity::deleteAll(['product_id' => $product->id]);

            // 5. Удаляем записи ImageEntity (если есть)
            if (!empty($imageIds)) {
                ImageEntity::deleteAll(['id' => $imageIds]);
            }

            // 6. Удаляем связи товара с категориями
            ProductCategoryEntity::deleteAll(['product_id' => $product->id]);

            // 7. Обновляем заявки клиентов: устанавливаем product_id = null
            CustomerRequestEntity::updateAll(
                ['product_id' => null],
                ['product_id' => $product->id]
            );

            // 8. Удаляем сам товар
            if (!$product->delete()) {
                throw new \Exception('Не удалось удалить товар.');
            }

            $transaction->commit();
            Yii::$app->response->statusCode = 204;
            return null;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Ошибка при удалении товара #{$id}: " . $e->getMessage(), __METHOD__);
            throw new yii\web\ServerErrorHttpException('Ошибка при удалении товара и связанных данных.');
        }
    }

    /**
     * Формирует ProductResponseDto для товара со всеми актуальными связями.
     *
     * @param ProductEntity $product
     * @return ProductResponseDto
     */
    private function buildProductResponseDto(ProductEntity $product): ProductResponseDto
    {
        $product->refresh();

        $productImages = ProductImageEntity::find()
            ->with('imageEntity')
            ->where(['product_id' => $product->id])
            ->all();
        $productImageDtos = array_map(
            fn(ProductImageEntity $pi) => ProductImageResponseDto::create($pi),
            $productImages
        );

        $categoryDtos = [];
        foreach ($product->getCategories()->with('imageEntity')->all() as $category) {
            $categoryDtos[] = CategoryResponseDto::create($category, $category->imageEntity);
        }

        return ProductResponseDto::createFromProduct($product, $productImageDtos, $categoryDtos);
    }
}
