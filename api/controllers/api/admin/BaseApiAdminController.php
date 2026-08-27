<?php

namespace app\controllers\api\admin;

use Yii;
use app\controllers\api\BaseApiController;
use yii\web\ForbiddenHttpException;
use app\entities\UserEntity;

/**
 * Базовый админский абстрактный REST API контроллер 
 */
abstract class BaseApiAdminController extends BaseApiController
{

    /**
     * Подключаем Bearer-аутентификацию для ВСЕХ действий контроллера.
     * Все методы требуют авторизации (Bearer JWT).
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Применяем аутентификацию ко всем действиям (пустой массив = все)
        // $behaviors += $this->applyBearerAuth([]);

        $behaviors['authenticator'] = [
        'class' => \yii\filters\auth\CompositeAuth::class,
        'except' => ['options'], // preflight CORS без токена
        'authMethods' => [
            \yii\filters\auth\HttpBearerAuth::class,
            [
                'class' => \yii\filters\auth\QueryParamAuth::class,
                'tokenParam' => 'access_token',
            ],
        ],
    ];

        return $behaviors;
    }

    /**
     * Проверка прав доступа.
     * Доступ разрешён только ролям admin / Администратор / manager / Менеджер.
     */
    public function checkAccess($action = null, $model = null, $params = [])
    {
        /** @var UserEntity|null $currentUser */
        $currentUser = Yii::$app->user->identity;
        $allowedRoles = ['admin', 'manager'];
        if ($currentUser) {
            foreach ($allowedRoles as $role) {
                if ($currentUser->hasRole($role)) {
                    return;
                }
            }
        }
        throw new ForbiddenHttpException('Доступ разрешен только администраторам и менеджерам.');
    }
}
