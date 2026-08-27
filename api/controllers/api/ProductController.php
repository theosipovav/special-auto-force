<?php

namespace app\controllers\api;

use Yii;
use app\entities\ProductEntity;
use app\entities\CustomerRequestEntity;
use app\entities\ProductImageEntity;
use app\dtos\response\ProductResponseDto;
use app\dtos\response\ProductShortResponseDto;
use app\dtos\response\ProductImageResponseDto;
use app\dtos\response\CategoryResponseDto;
use yii\web\NotFoundHttpException;

/**
 * Публичный REST API контроллер товаров (витрина магазина).
 *
 * Все методы доступны без авторизации любому пользователю.
 *
 * @SWG\Tag(
 *     name="public / product controller",
 *     description="Публичная витрина товаров."
 * )
 */
class ProductController extends BaseApiController
{
    public $modelClass = ProductEntity::class;

    /**
     * Отключаем стандартные CRUD-действия родителя (create/update/delete/view),
     * так как витрина предоставляет только чтение.
     */
    public function actions()
    {
        // Возвращаем пустой массив — стандартные действия не нужны
        return [];
    }

    /**
     * Список товаров с фильтрацией, пагинацией и сортировкой.
     *
     * @SWG\Get(
     *     path="/products",
     *     tags={"public / product controller"},
     *     operationId="publicProductIndex",
     *     summary="Список товаров (краткая информация)",
     *     description="Возвращает постраничный список товаров с поиском по названию/артикулу и сортировкой по цене/названию.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="categoryId", in="query", type="integer", description="Фильтр по ID категории"),
     *     @SWG\Parameter(name="inStock", in="query", type="boolean", description="Фильтр по наличию на складе"),
     *     @SWG\Parameter(name="q", in="query", type="string", description="Поиск по названию (title) или артикулу (article)"),
     *     @SWG\Parameter(name="sort", in="query", type="string", enum={"price_asc", "price_desc", "title_asc", "title_desc"}, description="Сортировка"),
     *     @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы (по умолчанию 1)"),
     *     @SWG\Parameter(name="per-page", in="query", type="integer", description="Элементов на странице (по умолчанию 20)"),
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/ProductShortResponseDto")),
     *         @SWG\Property(property="_links", type="object"),
     *         @SWG\Property(property="_meta", type="object")
     *     ))
     * )
     */
    public function actionIndex()
    {
        // Для краткого DTO категории не нужны, убираем их из with для оптимизации запросов
        $query = ProductEntity::find()->with(['productImages.imageEntity']);

        // 1. Фильтр по категории
        $categoryId = Yii::$app->request->get('categoryId');
        if (!empty($categoryId)) {
            $query->joinWith('productCategories')
                ->andWhere(['{{%product_category}}.category_id' => (int) $categoryId]);
        }

        // 2. Фильтр по наличию
        $inStock = Yii::$app->request->get('inStock');
        if ($inStock !== null && $inStock !== '') {
            $query->andWhere(['in_stock' => ($inStock === '1' || $inStock === 'true' || $inStock === 1) ? 1 : 0]);
        }

        // 3. Поиск по title и article
        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', '{{%product}}.title', $search],
                ['like', '{{%product}}.article', $search],
            ]);
        }

        // 4. Сортировка по title и price
        $sortParam = Yii::$app->request->get('sort');
        $order = ['id' => SORT_DESC];
        if ($sortParam === 'price_asc') {
            $order = ['price' => SORT_ASC];
        } elseif ($sortParam === 'price_desc') {
            $order = ['price' => SORT_DESC];
        } elseif ($sortParam === 'title_asc') {
            $order = ['title' => SORT_ASC];
        } elseif ($sortParam === 'title_desc') {
            $order = ['title' => SORT_DESC];
        }

        $query->orderBy($order);

        return $this->paginate($query);
    }


    /**
     * Получение подробной информации о товаре по ID.
     *
     * @SWG\Get(
     *     path="/products/{id}",
     *     tags={"public / product controller"},
     *     operationId="publicProductView",
     *     summary="Получить товар по ID (полная информация)",
     *     description="Возвращает детальную карточку товара со всеми изображениями и категориями.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID товара"),
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(ref="#/definitions/ProductResponseDto")),
     *     @SWG\Response(response=404, description="Товар не найден")
     * )
     */
    public function actionView(int $id)
    {
        $product = ProductEntity::find()
            ->where(['id' => $id])
            ->with(['categories.imageEntity', 'categories.productCategories', 'productImages.imageEntity'])
            ->one();

        if (!$product) {
            throw new NotFoundHttpException("Товар #{$id} не найден.");
        }

        return $this->buildProductResponseDto($product);
    }

    /**
     * Последние добавленные товары.
     *
     * @SWG\Get(
     *     path="/products/latest",
     *     tags={"public / product controller"},
     *     operationId="publicProductLatest",
     *     summary="Последние добавленные товары",
     *     description="Возвращает список из 10 последних добавленных товаров (по дате created_at).",
     *     produces={"application/json"},
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="success", type="boolean"),
     *         @SWG\Property(property="count", type="integer"),
     *         @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/ProductShortResponseDto"))
     *     ))
     * )
     */
    public function actionLatest()
    {
        $limit = Yii::$app->request->get('limit');
        if (empty($limit)){
            $limit = 10;
        }
        $products = ProductEntity::find()
            ->with(['productImages.imageEntity'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        $items = array_map(fn($p) => $this->buildProductShortResponseDto($p)->toArray(), $products);
        return $items;
    }

    /**
     * Самые популярные товары.
     *
     * @SWG\Get(
     *     path="/products/popular",
     *     tags={"public / product controller"},
     *     operationId="publicProductPopular",
     *     summary="Самые популярные товары",
     *     description="Возвращает список из 10 самых популярных товаров на основе заявок клиентов за последний год (12 месяцев). Отмененные заявки не учитываются.",
     *     produces={"application/json"},
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="success", type="boolean"),
     *         @SWG\Property(property="count", type="integer"),
     *         @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/ProductShortResponseDto"))
     *     ))
     * )
     */
    public function actionPopular()
    {

        $limit = Yii::$app->request->get('limit');
        if (empty($limit)){
            $limit = 10;
        }

        $oneYearAgo = date('Y-m-d H:i:s', strtotime('-1 year'));

        // Получаем ID товаров, отсортированные по количеству заявок за последний год
        $productIds = CustomerRequestEntity::find()
            ->select(['product_id'])
            ->where(['>=', 'created_at', $oneYearAgo])
            ->andWhere(['not', ['product_id' => null]])
            ->andWhere(['!=', 'status', CustomerRequestEntity::STATUS_CANCELLED])
            ->groupBy('product_id')
            ->orderBy(['COUNT(*)' => SORT_DESC])
            ->limit($limit)
            ->column();


        if (empty($productIds)) {
            $productIds = ProductEntity::find()
                ->select(['id'])
                ->orderBy(new \yii\db\Expression('RAND()'))
                ->limit($limit)
                ->column();
            if (empty($productIds)) {
                throw new NotFoundHttpException("Каталог продукции пуст");
            }
            $products = ProductEntity::find()->where(['id' => $productIds])->with(['productImages.imageEntity'])->all();
            $items = array_map(fn($p) => $this->buildProductShortResponseDto($p)->toArray(), $products);
        } else {
            $products = ProductEntity::find()->where(['id' => $productIds])->with(['productImages.imageEntity'])->all();
            $productsMap = [];
            foreach ($products as $p) {
                $productsMap[$p->id] = $p;
            }
            $sortedProducts = [];
            foreach ($productIds as $id) {
                if (isset($productsMap[$id])) {
                    $sortedProducts[] = $productsMap[$id];
                }
            }
            $items = array_map(fn($p) => $this->buildProductShortResponseDto($p)->toArray(), $sortedProducts);
        }
        return $items;
    }

    /**
     * Быстрый поиск и автодополнение.
     *
     * @SWG\Get(
     *     path="/products/search",
     *     tags={"public / product controller"},
     *     operationId="publicProductSearch",
     *     summary="Поиск товаров",
     *     description="Быстрый поиск и автодополнение по названию (title) и артикулу (article).",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="q", in="query", type="string", required=true, description="Поисковый запрос"),
     *     @SWG\Parameter(name="limit", in="query", type="integer", description="Лимит результатов (по умолчанию 10)"),
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="success", type="boolean"),
     *         @SWG\Property(property="query", type="string"),
     *         @SWG\Property(property="total", type="integer"),
     *         @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/ProductShortResponseDto"))
     *     ))
     * )
     */
    public function actionSearch($q = '')
    {
        if (empty($q)) {
            return ['success' => true, 'query' => $q, 'total' => 0, 'items' => []];
        }

        $query = ProductEntity::find()
            ->with(['productImages.imageEntity'])
            ->andFilterWhere([
                'or',
                ['like', 'title', $q],
                ['like', 'article', $q],
            ]);

        $limit = (int) Yii::$app->request->get('limit', 10);
        $products = $query->limit($limit)->all();

        $items = array_map(fn($p) => $this->buildProductShortResponseDto($p)->toArray(), $products);

        return [
            'success' => true,
            'query' => $q,
            'total' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Формирует ответ с пагинацией и массивом ProductShortResponseDto.
     */
    protected function paginate(\yii\db\ActiveQuery $query, int $defaultPageSize = 20): array
    {
        $request = Yii::$app->request;
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per-page', $defaultPageSize);

        if ($perPage < 1) $perPage = $defaultPageSize;
        if ($page < 1) $page = 1;

        $countQuery = clone $query;
        $totalCount = (int) $countQuery->count();

        $pageCount = $perPage > 0 ? (int) ceil($totalCount / $perPage) : 0;

        $products = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all();

        $items = [];
        foreach ($products as $product) {
            $items[] = $this->buildProductShortResponseDto($product)->toArray();
        }

        return [
            'items' => $items,
            '_links' => [
                'self' => ['href' => $request->absoluteUrl],
            ],
            '_meta' => [
                'totalCount' => $totalCount,
                'pageCount' => $pageCount,
                'currentPage' => $page,
                'perPage' => $perPage,
            ],
        ];
    }

    /**
     * Формирует ProductResponseDto (полная карточка товара).
     * Использует жадно загруженные связи, не делает лишних SQL-запросов.
     */
    private function buildProductResponseDto(ProductEntity $product): ProductResponseDto
    {
        $productImageDtos = array_map(
            fn(ProductImageEntity $pi) => ProductImageResponseDto::create($pi),
            $product->productImages
        );

        $categoryDtos = array_map(
            fn($category) => CategoryResponseDto::create(
                $category,
                $category->imageEntity,
                count($category->productCategories)
            ),
            $product->categories
        );

        return ProductResponseDto::createFromProduct($product, $productImageDtos, $categoryDtos);
    }


    /**
     * Формирует ProductShortResponseDto (краткая карточка для списков).
     * Использует жадно загруженные связи, не делает лишних SQL-запросов.
     */
    private function buildProductShortResponseDto(ProductEntity $product): ProductShortResponseDto
    {
        $productImageDtos = array_map(
            fn(ProductImageEntity $pi) => ProductImageResponseDto::create($pi),
            $product->productImages
        );

        // Передаем пустой массив вместо categoryDtos, так как они не используются в Short DTO
        return ProductShortResponseDto::createFromProduct($product, $productImageDtos, $product->categories);
    }
}
