<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\ImageEntity;
use yii\web\UploadedFile;

/**
 * REST API контроллер изображений (ImageEntity)
 */
class ImageController extends BaseApiController
{
    public $modelClass = ImageEntity::class;

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
            $query = ImageEntity::find();

            $title = Yii::$app->request->get('title');
            if (!empty($title)) {
                $query->andWhere(['like', 'title', $title]);
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
     * POST /api/images
     * Создание нового изображения
     */
    public function actionCreate()
    {
        $image = new ImageEntity();
        
        $image->title = Yii::$app->request->getBodyParam('title', '');
        $image->path = Yii::$app->request->getBodyParam('path', '');
        $image->url = Yii::$app->request->getBodyParam('url', '');

        if ($image->save()) {
            return [
                'success' => true,
                'message' => 'Изображение успешно создано',
                'data' => $image->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'errors' => $image->getErrors(),
        ];
    }

    /**
     * PUT/PATCH /api/images/{id}
     * Обновление изображения
     */
    public function actionUpdate($id)
    {
        $image = ImageEntity::findOne($id);
        if (!$image) {
            throw new \yii\web\NotFoundHttpException("Изображение не найдено.");
        }

        $image->load(Yii::$app->request->getBodyParams(), '');
        
        if ($image->save()) {
            return [
                'success' => true,
                'message' => 'Изображение успешно обновлено',
                'data' => $image->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'errors' => $image->getErrors(),
        ];
    }

    /**
     * DELETE /api/images/{id}
     * Удаление изображения
     */
    public function actionDelete($id)
    {
        $image = ImageEntity::findOne($id);
        if (!$image) {
            throw new \yii\web\NotFoundHttpException("Изображение не найдено.");
        }

        if ($image->delete()) {
            Yii::$app->response->statusCode = 204;
            return null;
        }

        Yii::$app->response->statusCode = 500;
        return [
            'success' => false,
            'errors' => $image->getErrors(),
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
