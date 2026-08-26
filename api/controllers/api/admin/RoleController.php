<?php

namespace app\controllers\api\admin;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use app\models\entities\Role;
use app\models\entities\User;
use app\controllers\api\BaseApiController;

/**
 * REST API контроллер ролей пользователей (Role)
 */
class RoleController extends BaseApiController
{
    public $modelClass = Role::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'delete'],
        ];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete'])) {
            /** @var User $currentUser */
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser || (!$currentUser->hasRole('admin') && !$currentUser->hasRole('Администратор'))) {
                throw new ForbiddenHttpException('Управление ролями доступно только администратору.');
            }
        }
    }
}
