<?php

namespace app\controllers\api;

use Yii;
use app\models\entities\Product;
use app\models\dtos\response\ProductResponseDto;
use app\models\dtos\response\ProductImageResponseDto;

/**
 * Публичный REST API контроллер товаров (витрина магазина).
 *
 * Все методы доступны без авторизации любому пользователю.
 * Возвращает данные в виде ProductResponseDto.
 *
 * Маршруты:
 *  - GET /api/products          — список с фильтрацией и пагинацией
 *  - GET /api/products/latest   — последние добавленные
 *  - GET /api/products/popular  — популярные
 *  - GET /api/products/search   — поиск / автодополнение
 */
class ProductController extends BaseApiController
{
    public $modelClass = Product::class;

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
     * GET /api/products
     * Список товаров с фильтрацией, пагинацией и сортировкой.
     */
    public function actionIndex()
    {
        $query = Product::find()->with(['productImages.imageEntity']);

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

        // 3. Поиск
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

        // 4. Сортировка
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

        $query->orderBy($order);

        return $this->pagination($query, 50);
    }

    /**
     * GET /api/products/latest
     * Карусель: последние добавленные товары.
     */
    public function actionLatest()
    {
        $limit = (int) Yii::$app->request->get('limit', 10);
        $products = Product::find()
            ->with(['productImages.imageEntity'])
            ->orderBy(['id' => SORT_DESC])
            ->limit($limit)
            ->all();

        $items = array_map(fn($p) => $this->mapToProductResponseDto($p)->toArray(), $products);

        return [
            'success' => true,
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * GET /api/products/popular
     * Карусель: самые популярные товары.
     */
    public function actionPopular()
    {
        $limit = (int) Yii::$app->request->get('limit', 10);
        $products = Product::find()
            ->with(['productImages.imageEntity'])
            ->orderBy(['id' => SORT_DESC])
            ->limit($limit)
            ->all();

        $items = array_map(fn($p) => $this->mapToProductResponseDto($p)->toArray(), $products);

        return [
            'success' => true,
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * GET /api/products/search?q=...
     * Быстрый поиск и автодополнение.
     */
    public function actionSearch($q = '')
    {
        if (empty($q)) {
            return ['success' => true, 'total' => 0, 'items' => []];
        }

        $query = Product::find()
            ->with(['productImages.imageEntity'])
            ->andFilterWhere([
                'or',
                ['like', 'title', $q],
                ['like', 'article', $q],
                ['like', 'short_description', $q],
                ['like', 'long_description', $q],
            ]);

        $limit = (int) Yii::$app->request->get('limit', 20);
        $products = $query->limit($limit)->all();

        $items = array_map(fn($p) => $this->mapToProductResponseDto($p)->toArray(), $products);

        return [
            'success' => true,
            'query' => $q,
            'total' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Формирует ответ с пагинацией и массивом ProductResponseDto.
     *
     * @param \yii\db\ActiveQuery $query
     * @param int $defaultPageSize
     * @return array
     */
    protected function pagination(\yii\db\ActiveQuery $query, int $defaultPageSize = 50): array
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
            $items[] = $this->mapToProductResponseDto($product)->toArray();
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
     * Преобразование модели Product в ProductResponseDto.
     */
    private function mapToProductResponseDto(Product $product): ProductResponseDto
    {
        $imagesDto = [];
        foreach ($product->productImages as $img) {
            $url = $img->imageEntity ? $img->imageEntity->url : '';
            $imagesDto[] = new ProductImageResponseDto(
                (int) $img->product_id,
                (int) $img->image_id,
                (bool) $img->is_main,
                (string) $img->title,
                (string) $url
            );
        }

        return new ProductResponseDto(
            (int) $product->id,
            (string) $product->title,
            (string) $product->short_description,
            (string) $product->long_description,
            $product->info,
            $product->article,
            $product->price !== null ? (float) $product->price : null,
            (int) $product->in_stock,
            $imagesDto,
            $product->manufacturer,
            $product->country,
            (string) $product->created_at
        );
    }
}