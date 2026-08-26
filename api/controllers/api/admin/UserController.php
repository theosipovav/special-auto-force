<?php

namespace app\controllers\api\admin;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\UserEntity;
use app\models\entities\RoleEntity;
use app\controllers\api\BaseApiController;

/**
 * REST API контроллер пользователей (UserEntity)
 * 
 */
class UserController extends BaseApiController
{
    public $modelClass = UserEntity::class;

    /**
     * @SWG\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Получить список пользователей",
     *     description="Возвращает пагинированный список пользователей с возможностью фильтрации по роли и поиска",
     *     produces={"application/json"},
     *     @SWG\Parameter(name="role", in="query", type="string", description="Фильтр по названию роли"),
     *     @SWG\Parameter(name="q", in="query", type="string", description="Поисковый запрос по username, email, name, surname, phone"),
     *     @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы", default=1),
     *     @SWG\Parameter(name="per-page", in="query", type="integer", description="Количество элементов на странице", default=20),
     *     @SWG\Response(response=200, description="Список пользователей",
     *         @SWG\Schema(
     *             @SWG\Property(property="items", type="object", @SWG\Items(ref="#/definitions/UserResponseDto")),
     *             @SWG\Property(property="_meta", type="object")
     *         )
     *     )
     * )
     *
     * @SWG\Post(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Создать пользователя",
     *     description="Создание нового пользователя. Требуется авторизация.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     security={{"bearerAuth":{}}},
     *     @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/UserEntity")),
     *     @SWG\Response(response=201, description="Пользователь создан"),
     *     @SWG\Response(response=403, description="Доступ запрещен"),
     *     @SWG\Response(response=422, description="Ошибка валидации")
     * )
     *
     * @SWG\Get(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Получить пользователя по ID",
     *     description="Возвращает данные пользователя по идентификатору",
     *     produces={"application/json"},
     *     @SWG\Parameter(name="id", in="path", type="integer", required=true, description="ID пользователя"),
     *     @SWG\Response(response=200, description="Данные пользователя", @SWG\Schema(ref="#/definitions/UserEntity")),
     *     @SWG\Response(response=404, description="Пользователь не найден")
     * )
     *
     * @SWG\Put(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Обновить пользователя",
     *     description="Полное обновление данных пользователя. Требуется авторизация.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     security={{"bearerAuth":{}}},
     *     @SWG\Parameter(name="id", in="path", type="integer", required=true, description="ID пользователя"),
     *     @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/UserEntity")),
     *     @SWG\Response(response=200, description="Пользователь обновлен"),
     *     @SWG\Response(response=403, description="Доступ запрещен"),
     *     @SWG\Response(response=404, description="Пользователь не найден"),
     *     @SWG\Response(response=422, description="Ошибка валидации")
     * )
     *
     * @SWG\Patch(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Частично обновить пользователя",
     *     description="Частичное обновление данных пользователя. Требуется авторизация.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     security={{"bearerAuth":{}}},
     *     @SWG\Parameter(name="id", in="path", type="integer", required=true, description="ID пользователя"),
     *     @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/UserEntity")),
     *     @SWG\Response(response=200, description="Пользователь обновлен"),
     *     @SWG\Response(response=403, description="Доступ запрещен"),
     *     @SWG\Response(response=404, description="Пользователь не найден"),
     *     @SWG\Response(response=422, description="Ошибка валидации")
     * )
     *
     * @SWG\Delete(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Удалить пользователя",
     *     description="Удаление пользователя по ID. Требуется авторизация администратора.",
     *     produces={"application/json"},
     *     security={{"bearerAuth":{}}},
     *     @SWG\Parameter(name="id", in="path", type="integer", required=true, description="ID пользователя"),
     *     @SWG\Response(response=204, description="Пользователь удален"),
     *     @SWG\Response(response=403, description="Доступ запрещен"),
     *     @SWG\Response(response=404, description="Пользователь не найден")
     * )
     */

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
            $query = UserEntity::find();

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
     * @SWG\Post(
     *     path="/users/{id}/assign-role",
     *     tags={"Users"},
     *     summary="Назначить роль пользователю",
     *     description="Назначение роли пользователю по ID. Требуется авторизация.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     security={{"bearerAuth":{}}},
     *     @SWG\Parameter(name="id", in="path", type="integer", required=true, description="ID пользователя"),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             @SWG\Property(property="roleId", type="integer", description="ID роли"),
     *             @SWG\Property(property="role", type="string", description="Название роли (title)")
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Роль назначена"),
     *     @SWG\Response(response=400, description="Не указан roleId или role"),
     *     @SWG\Response(response=403, description="Доступ запрещен"),
     *     @SWG\Response(response=404, description="Пользователь не найден")
     * )
     */
    public function actionAssignRole($id)
    {
        $user = UserEntity::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("Пользователь #{$id} не найден.");
        }

        $roleId = Yii::$app->request->getBodyParam('roleId');
        if (empty($roleId)) {
            $roleTitle = Yii::$app->request->getBodyParam('role');
            if ($roleTitle) {
                $role = RoleEntity::findOne(['title' => $roleTitle]);
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
        $user = UserEntity::findOne($id);
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
            /** @var UserEntity $currentUser */
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
