<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\Category;
use app\models\entities\Product;

/**
 * REST API контроллер категорий (Category)
 */
class CategoryController extends BaseApiController
{
    public $modelClass = Category::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Protect write operations
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'delete'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Default list pagination & sorting
        $actions['index']['prepareDataProvider'] = function () {
            return new ActiveDataProvider([
                'query' => Category::find(),
                'pagination' => [
                    'pageSize' => 50,
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_ASC],
                ],
            ]);
        };

        return $actions;
    }

    /**
     * GET /api/categories/{id}/products
     * Получить все товары в данной категории с пагинацией (~50 товаров на страницу)
     */
    public function actionProducts($id)
    {
        $category = Category::findOne($id);
        if (!$category) {
            throw new NotFoundHttpException("Категория #{$id} не найдена.");
        }

        $query = $category->getProducts();

        $inStockOnly = Yii::$app->request->get('inStock');
        if ($inStockOnly !== null && ($inStockOnly === '1' || $inStockOnly === 'true')) {
            $query->andWhere(['in_stock' => 1]);
        }

        $sortParam = Yii::$app->request->get('sort', 'id_desc');
        $order = ['id' => SORT_DESC];
        if ($sortParam === 'price_asc') {
            $order = ['price' => SORT_ASC];
        } elseif ($sortParam === 'price_desc') {
            $order = ['price' => SORT_DESC];
        } elseif ($sortParam === 'popular') {
            $order = ['id' => SORT_DESC];
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50, // Постраничный вывод по ~50 позиций
            ],
            'sort' => [
                'defaultOrder' => $order,
            ],
        ]);
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete'])) {
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser || (!$currentUser->hasRole('admin') && !$currentUser->hasRole('Администратор'))) {
                throw new ForbiddenHttpException('Доступ разрешен только администраторам.');
            }
        }
    }
}
