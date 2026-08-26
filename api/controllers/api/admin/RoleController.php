<?php

namespace app\controllers\api\admin;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use app\models\entities\RoleEntity;
use app\models\entities\UserEntity;
use app\controllers\api\BaseApiController;

/**
 * REST API контроллер ролей пользователей (RoleEntity)
 */
class RoleController extends BaseApiController
{
    public $modelClass = RoleEntity::class;

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
            /** @var UserEntity $currentUser */
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser || (!$currentUser->hasRole('admin') && !$currentUser->hasRole('Администратор'))) {
                throw new ForbiddenHttpException('Управление ролями доступно только администратору.');
            }
        }
    }
}
