<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use app\models\entities\ParameterEntity;
use app\models\entities\UserEntity;

/**
 * REST API контроллер параметров сайта (ParameterEntity)
 */
class ParameterController extends BaseApiController
{
    public $modelClass = ParameterEntity::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Write operations protected with Bearer Auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'delete'],
        ];

        return $behaviors;
    }

    /**
     * GET /api/parameters/map
     * Получить словарь всех параметров сайта в виде ассоциативного массива [code => value]
     */
    public function actionMap()
    {
        return [
            'success' => true,
            'data' => ParameterEntity::getMap(),
        ];
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete'])) {
            /** @var UserEntity $currentUser */
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser || (!$currentUser->hasRole('admin') && !$currentUser->hasRole('Администратор'))) {
                throw new ForbiddenHttpException('Доступ разрешен только администраторам.');
            }
        }
    }
}
