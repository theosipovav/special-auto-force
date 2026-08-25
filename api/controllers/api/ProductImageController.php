<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\ProductImage;
use app\models\entities\ImageEntity;

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
            $query = ProductImage::find()->with(['imageEntity', 'product']);

            $productId = Yii::$app->request->get('productId');
            if (!empty($productId)) {
                $query->andWhere(['product_id' => (int) $productId]);
            }

            $isMain = Yii::$app->request->get('is_main');
            if ($isMain !== null && $isMain !== '') {
                $query->andWhere(['is_main' => ($isMain === '1' || $isMain === 'true') ? 1 : 0]);
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

    /**
     * POST /api/product-images
     * Создание связи изображения с товаром
     */
    public function actionCreate()
    {
        $product_id = Yii::$app->request->getBodyParam('product_id');
        $image_id = Yii::$app->request->getBodyParam('image_id');
        $title = Yii::$app->request->getBodyParam('title', '');
        $is_main = Yii::$app->request->getBodyParam('is_main', false);

        $productImage = new ProductImage();
        $productImage->product_id = (int) $product_id;
        $productImage->image_id = (int) $image_id;
        $productImage->title = $title;
        $productImage->is_main = (bool) $is_main;

        if ($productImage->save()) {
            return [
                'success' => true,
                'message' => 'Изображение успешно привязано к товару',
                'data' => $productImage->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'errors' => $productImage->getErrors(),
        ];
    }

    /**
     * PUT/PATCH /api/product-images/{product_id}/{image_id}
     * Обновление связи изображения с товаром
     */
    public function actionUpdate($product_id, $image_id)
    {
        $productImage = ProductImage::findOne(['product_id' => $product_id, 'image_id' => $image_id]);
        if (!$productImage) {
            throw new \yii\web\NotFoundHttpException("Связь не найдена.");
        }

        $productImage->load(Yii::$app->request->getBodyParams(), '');
        
        if ($productImage->save()) {
            return [
                'success' => true,
                'message' => 'Связь успешно обновлена',
                'data' => $productImage->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'errors' => $productImage->getErrors(),
        ];
    }

    /**
     * DELETE /api/product-images/{product_id}/{image_id}
     * Удаление связи изображения с товаром
     */
    public function actionDelete($product_id, $image_id)
    {
        $productImage = ProductImage::findOne(['product_id' => $product_id, 'image_id' => $image_id]);
        if (!$productImage) {
            throw new \yii\web\NotFoundHttpException("Связь не найдена.");
        }

        if ($productImage->delete()) {
            Yii::$app->response->statusCode = 204;
            return null;
        }

        Yii::$app->response->statusCode = 500;
        return [
            'success' => false,
            'errors' => $productImage->getErrors(),
        ];
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
