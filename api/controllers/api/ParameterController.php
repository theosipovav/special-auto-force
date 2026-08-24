<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use app\models\entities\Parameter;

/**
 * REST API контроллер параметров сайта (Parameter)
 */
class ParameterController extends BaseApiController
{
    public $modelClass = Parameter::class;

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
            'data' => Parameter::getMap(),
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
