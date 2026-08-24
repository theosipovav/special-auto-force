<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\ProductImage;

/**
 * REST API контроллер изображений товаров (ProductImage)
 */
class ProductImageController extends BaseApiController
{
    public $modelClass = ProductImage::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'delete'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        $actions['index']['prepareDataProvider'] = function () {
            $query = ProductImage::find();

            $productId = Yii::$app->request->get('productId');
            if (!empty($productId)) {
                $query->andWhere(['product_id' => (int) $productId]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        };

        return $actions;
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
