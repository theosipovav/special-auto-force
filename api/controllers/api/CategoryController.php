<?php

namespace app\controllers\api;

use yii\data\ActiveDataProvider;
use app\models\entities\CategoryEntity;
use app\models\dtos\response\CategoryResponseDto;
use Yii;

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
        $query = CategoryEntity::find()->with(['imageEntity']);

        // Поиск по названию (опционально)
        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere(['like', 'title', $search]);
        }

        $categories = $query->orderBy(['id' => SORT_ASC])->all();

        $items = array_map(
            fn(CategoryEntity $c) => $this->buildCategoryResponseDto($c)->toArray(),
            $categories
        );

        return $items;
    }

    /**
     * Формирует CategoryResponseDto для категории с учётом связанного изображения.
     *
     * @param CategoryEntity $category
     * @return CategoryResponseDto
     */
    private function buildCategoryResponseDto(CategoryEntity $category): CategoryResponseDto
    {
        return CategoryResponseDto::create($category, $category->imageEntity);
    }
}
