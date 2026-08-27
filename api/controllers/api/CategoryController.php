<?php

namespace app\controllers\api;

use app\entities\CategoryEntity;
use app\entities\ProductEntity;
use app\dtos\response\CategoryResponseDto;
use app\dtos\response\ProductShortResponseDto;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Публичный REST API контроллер категорий (витрина магазина).
 *
 * Все методы доступны без авторизации любому пользователю.
 * Возвращает данные в виде CategoryResponseDto.
 *
 * @SWG\Tag(
 *     name="public / category controller",
 *     description="Публичная витрина категорий товаров."
 * )
 */
class CategoryController extends BaseApiController
{
    public $modelClass = CategoryEntity::class;

    /**
     * Отключаем стандартные CRUD-действия родителя (create/update/delete/view),
     * так как витрина предоставляет только чтение.
     */
    public function actions()
    {
        return [];
    }



    /**
     * Список всех категорий продукции.
     *
     * @SWG\Get(
     *     path="/categories",
     *     tags={"public / category controller"},
     *     operationId="publicCategoryIndex",
     *     summary="Список всех категорий",
     *     description="Возвращает плоский список всех категорий товаров с изображениями. Сортировка по возрастанию ID.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="q", in="query", type="string", description="Поиск по названию категории"),
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/CategoryResponseDto")))
     * )
     */
    public function actionIndex()
    {
        $query = CategoryEntity::find()->with(['imageEntity','productCategories']);

        // Поиск по названию (опционально)
        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere(['like', 'title', $search]);
        }

        $categories = $query->orderBy(['id' => SORT_ASC])->all();
        
        $items = array_map(
            fn(CategoryEntity $c) => CategoryResponseDto::create($c, $c->imageEntity, count($c->productCategories))->toArray(),
            $categories
        );

        return $items;
    }


    /**
     * Список товаров в указанной категории.
     *
     * @SWG\Get(
     *     path="/categories/{id}/products",
     *     tags={"public / category controller"},
     *     operationId="publicCategoryProducts",
     *     summary="Товары категории",
     *     description="Возвращает список товаров для указанной категории в виде ProductShortResponseDto.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *         description="ID категории"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/ProductShortResponseDto")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Категория не найдена"
     *     )
     * )
     */
    public function actionProducts(int $id): array
    {
        $category = CategoryEntity::findOne($id);
        if (!$category) {
            throw new NotFoundHttpException("Категория с ID {$id} не найдена.");
        }

        // Получаем товары категории с подгрузкой главного изображения
        $products = $category->getProducts()
            ->with(['mainImage'])
            ->all();

        // Преобразуем каждый товар в DTO



        return array_map(
            fn(ProductEntity $product) => $this->buildProductShortResponseDto($product, [$category])->toArray(),
            $products
        );
    }

    /**
     * Формирует ProductShortResponseDto для товара.
     *
     * @param ProductEntity $product
     * @param array $categories
     * @return ProductShortResponseDto
     */
    private function buildProductShortResponseDto(ProductEntity $product, $categories): ProductShortResponseDto
    {
        $mainImageUrl = '';
        $mainImage = $product->getMainImage()->one();
        if ($mainImage) {
            $mainImageUrl = $mainImage->url;
        }
        $categoryTitles = array_map(fn($c)=> $c->title, $categories);
        return new ProductShortResponseDto(
            $product->id,
            $product->title,
            $product->price,
            (int) $product->in_stock,
            $mainImageUrl,
            $categoryTitles
        );
    }
}
