<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\User;
use app\models\entities\Role;

/**
 * REST API контроллер пользователей (User)
 */
class UserController extends BaseApiController
{
    public $modelClass = User::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Protect user modification endpoints with Bearer Auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'delete', 'assign-role', 'revoke-role'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Custom prepareDataProvider for filtering and searching users
        $actions['index']['prepareDataProvider'] = function ($action) {
            $query = User::find();

            $role = Yii::$app->request->get('role');
            if (!empty($role)) {
                $query->joinWith('roles')
                      ->andWhere(['{{%role}}.title' => $role]);
            }

            $search = Yii::$app->request->get('q');
            if (!empty($search)) {
                $query->andFilterWhere(['or',
                    ['like', 'username', $search],
                    ['like', 'email', $search],
                    ['like', 'name', $search],
                    ['like', 'surname', $search],
                    ['like', 'phone', $search],
                ]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        };

        return $actions;
    }

    /**
     * POST /api/users/{id}/roles
     * Назначить роль пользователю
     */
    public function actionAssignRole($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("Пользователь #{$id} не найден.");
        }

        $roleId = Yii::$app->request->getBodyParam('roleId');
        if (empty($roleId)) {
            $roleTitle = Yii::$app->request->getBodyParam('role');
            if ($roleTitle) {
                $role = Role::findOne(['title' => $roleTitle]);
                $roleId = $role ? $role->id : null;
            }
        }

        if (!$roleId) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Необходимо указать roleId или role (title).'];
        }

        $success = $user->assignRole((int) $roleId);
        return [
            'success' => $success,
            'message' => $success ? 'Роль успешно назначена' : 'Ошибка при назначении роли',
            'user' => $user->toArray(),
        ];
    }

    /**
     * DELETE /api/users/{id}/roles/{roleId}
     * Отозвать роль у пользователя
     */
    public function actionRevokeRole($id, $roleId)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("Пользователь #{$id} не найден.");
        }

        $success = $user->revokeRole((int) $roleId);
        return [
            'success' => $success,
            'message' => $success ? 'Роль успешно отозвана' : 'Ошибка при отзыве роли',
            'user' => $user->toArray(),
        ];
    }

    /**
     * Check permissions before actions
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete', 'assign-role', 'revoke-role'])) {
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser) {
                throw new ForbiddenHttpException('Требуется авторизация.');
            }

            // Allow user to edit their own profile, or require admin role
            if ($action === 'update' && $model && $model->id === $currentUser->id) {
                return;
            }

            if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('Администратор')) {
                throw new ForbiddenHttpException('Недостаточно прав для выполнения действия.');
            }
        }
    }
}
