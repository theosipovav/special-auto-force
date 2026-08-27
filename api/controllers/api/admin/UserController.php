<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\ServerErrorHttpException;
use app\entities\UserEntity;
use app\entities\RoleEntity;
use app\dtos\request\UserRequestDto;
use app\dtos\request\PasswordSetRequestDto;

/**
 * Пользователи (администратор)
 *
 * @SWG\Tag(
 *     name="admin / user controller",
 *     description="Управление пользователями и их ролями."
 * )
 */
class UserController extends BaseApiAdminController
{
    public $modelClass = UserEntity::class;


    /**
     * Настройка стандартных CRUD-действий.
     * Разрешены: index, view, update, delete.
     * Создание пользователей выполняется через публичный endpoint /auth/signup.
     * 
     * @SWG\Get(
     *   tags={"admin / user controller"},
     *   path="/admin/users",
     *   summary="Список пользователей",
     *   description="Возвращает список пользователей с пагинацией и фильтрацией по роли, статусу и общему поиску.",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="role", in="query", type="string", description="Фильтр по названию роли (admin, manager, customer)"),
     *   @SWG\Parameter(name="status", in="query", type="integer", enum={0, 9, 10}, description="Фильтр по статусу: 0 — удалён, 9 — неактивен, 10 — активен"),
     *   @SWG\Parameter(name="q", in="query", type="string", description="Поиск по логину, email, ФИО, телефону"),
     *   @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы (по умолчанию 1)"),
     *   @SWG\Parameter(name="per-page", in="query", type="integer", description="Количество записей на странице (по умолчанию 20)"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/UserEntity")),
     *       @SWG\Property(property="_links", type="object"),
     *       @SWG\Property(property="_meta", type="object")
     *   )),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     * 
     * @SWG\Get(
     *   tags={"admin / user controller"},
     *   path="/admin/user/{id}",
     *   summary="Получить пользователя по ID",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *   @SWG\Parameter(name="expand", in="query", type="string", description="Дополнительные поля: roles, userRoles"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(ref="#/definitions/UserEntity")),
     *   @SWG\Response(response=404, description="Пользователь не найден"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     * 
     * @SWG\Delete(
     *   tags={"admin / user controller"},
     *   path="/admin/user/{id}",
     *   summary="Удалить пользователя",
     *   description="Удаляет пользователя и все его связи с ролями (каскадно). Нельзя удалить собственную учётную запись.",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *
     *   @SWG\Response(response=204, description="Пользователь успешно удалён"),
     *   @SWG\Response(response=404, description="Пользователь не найден"),
     *   @SWG\Response(response=403, description="Доступ запрещён (в т.ч. попытка удалить себя)"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actions()
    {
        $actions = parent::actions();

        // Разрешённые стандартные действия
        $allowed = ['index', 'view', 'delete'];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }

        // Кастомный DataProvider для index
        $actions['index']['prepareDataProvider'] = function () {
            $query = UserEntity::find()->with('roles');

            // Фильтр по роли (название)
            $role = Yii::$app->request->get('role');
            if (!empty($role)) {
                $query->joinWith('roles')
                    ->andWhere(['{{%role}}.title' => $role]);
            }

            // Фильтр по статусу
            $status = Yii::$app->request->get('status');
            if ($status !== null && $status !== '') {
                $query->andWhere(['status' => (int) $status]);
            }

            // Общий поиск
            $search = Yii::$app->request->get('q');
            if (!empty($search)) {
                $query->andFilterWhere([
                    'or',
                    ['like', '{{%user}}.username', $search],
                    ['like', '{{%user}}.email', $search],
                    ['like', '{{%user}}.name', $search],
                    ['like', '{{%user}}.surname', $search],
                    ['like', '{{%user}}.patronymic', $search],
                    ['like', '{{%user}}.phone', $search],
                ]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                    'pageSizeParam' => 'per-page',
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        };

        return $actions;
    }


    /**
     * Создание нового пользователя.
     *
     * @SWG\Post(
     *   tags={"admin / user controller"},
     *   path="/admin/user",
     *   summary="Создать пользователя",
     *   description="Создает нового пользователя. После создания автоматически назначается роль 'customer' по умолчанию.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/UserRequestDto")),
     *
     *   @SWG\Response(response=201, description="Пользователь успешно создан", @SWG\Schema(ref="#/definitions/UserEntity")),
     *   @SWG\Response(response=422, description="Ошибка валидации данных"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionCreate()
    {
        $this->checkAccess();

        $dto = new UserRequestDto();
        $dto->scenario = UserRequestDto::SCENARIO_CREATE;
        $dto->load(Yii::$app->request->getBodyParams(), '');

        if (!$dto->validate()) {
            $errorString = json_encode($dto->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации UserRequestDto (create): ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Ошибка валидации: ' . $errorString);
        }

        // Проверка уникальности логина
        $existingUser = UserEntity::find()->where(['username' => $dto->userтame])->one();
        if ($existingUser) {
            throw new UnprocessableEntityHttpException("Пользователь с логином '{$dto->userтame}' уже существует.");
        }

        // Проверка уникальности email
        if (!empty($dto->email)) {
            $existingEmail = UserEntity::find()->where(['email' => $dto->email])->one();
            if ($existingEmail) {
                throw new UnprocessableEntityHttpException("Email '{$dto->email}' уже зарегистрирован.");
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new UserEntity();
            $user->username = $dto->userтame;
            $user->email = $dto->email ?? '';
            $user->phone = $dto->phone ?? '';
            $user->name = $dto->name;
            $user->surname = $dto->surname;
            $user->patronymic = $dto->patronymic;
            $user->address = $dto->address;
            $user->date_of_birth = $dto->dateOfBirth;
            $user->password = $dto->password; // Хешируется в beforeSave()
            $user->status = UserEntity::STATUS_ACTIVE;

            if (!$user->save()) {
                $errorString = json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                Yii::error('Ошибка сохранения пользователя: ' . $errorString, __METHOD__);
                throw new \Exception('Не удалось сохранить пользователя: ' . $errorString);
            }

            // Автоматически назначаем роль 'customer' по умолчанию
            $customerRole = RoleEntity::findOne(['title' => 'customer']);
            if ($customerRole) {
                $user->assignRole($customerRole->id);
            }

            $transaction->commit();

            $user->refresh();
            Yii::$app->response->statusCode = 201;
            return $user;
        } catch (UnprocessableEntityHttpException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Ошибка создания пользователя: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось создать пользователя.');
        }
    }


    /**
     * Обновление пользователя.
     *
     * @SWG\Put(
     *   tags={"admin / user controller"},
     *   path="/admin/user/{id}",
     *   summary="Обновить пользователя",
     *   description="Обновляет данные пользователя. Пароль меняется только если переданы оба поля (password и passwordRepeat).",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *   @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/UserRequestDto")),
     *
     *   @SWG\Response(response=200, description="Пользователь обновлён", @SWG\Schema(ref="#/definitions/UserEntity")),
     *   @SWG\Response(response=404, description="Пользователь не найден"),
     *   @SWG\Response(response=422, description="Ошибка валидации данных"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionUpdate(int $id)
    {
        $this->checkAccess('update');

        /** @var UserEntity|null $user */
        $user = UserEntity::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("Пользователь #{$id} не найден.");
        }

        $dto = new UserRequestDto();
        $dto->scenario = UserRequestDto::SCENARIO_UPDATE;
        $dto->load(Yii::$app->request->getBodyParams(), '');

        if (!$dto->validate()) {
            $errorString = json_encode($dto->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации UserRequestDto (update): ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Ошибка валидации: ' . $errorString);
        }

        // Проверка уникальности логина (если меняется)
        if ($dto->userтame !== null && $dto->userтame !== $user->username) {
            $existingUser = UserEntity::find()
                ->where(['username' => $dto->userтame])
                ->andWhere(['!=', 'id', $user->id])
                ->one();
            if ($existingUser) {
                throw new UnprocessableEntityHttpException("Логин '{$dto->userтame}' уже занят.");
            }
            $user->username = $dto->userтame;
        }

        // Проверка уникальности email (если меняется)
        if ($dto->email !== null && $dto->email !== $user->email) {
            $existingEmail = UserEntity::find()
                ->where(['email' => $dto->email])
                ->andWhere(['!=', 'id', $user->id])
                ->one();
            if ($existingEmail) {
                throw new UnprocessableEntityHttpException("Email '{$dto->email}' уже зарегистрирован.");
            }
            $user->email = $dto->email;
        }

        // Обновляем остальные поля
        if ($dto->phone !== null) $user->phone = $dto->phone;
        if ($dto->name !== null) $user->name = $dto->name;
        if ($dto->surname !== null) $user->surname = $dto->surname;
        if ($dto->patronymic !== null) $user->patronymic = $dto->patronymic;
        if ($dto->address !== null) $user->address = $dto->address;
        if ($dto->dateOfBirth !== null) $user->date_of_birth = $dto->dateOfBirth;
        if ($dto->status !== null) $user->status = $dto->status;

        // Пароль обновляем только если он передан
        if (!empty($dto->password)) {
            $user->password = $dto->password; // Хешируется в beforeSave()
        }

        if (!$user->save()) {
            $errorString = json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка обновления пользователя: ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Ошибка обновления: ' . $errorString);
        }

        $user->refresh();
        return $user;
    }


    /**
     * Установка нового пароля пользователю.
     *
     * @SWG\Post(
     *   tags={"admin / user controller"},
     *   path="/admin/user/{id}/password-set",
     *   summary="Установить новый пароль",
     *   description="Устанавливает новый пароль для указанного пользователя. Старый пароль не требуется.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *   @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/PasswordSetRequestDto")),
     *
     *   @SWG\Response(response=200, description="Пароль успешно установлен",
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="success", type="boolean", example=true),
     *           @SWG\Property(property="message", type="string", example="Пароль успешно изменён"),
     *           @SWG\Property(property="user", ref="#/definitions/UserEntity")
     *       )
     *   ),
     *   @SWG\Response(response=404, description="Пользователь не найден"),
     *   @SWG\Response(response=422, description="Ошибка валидации пароля"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionPasswordSet(int $id)
    {
        $this->checkAccess('update');

        /** @var UserEntity|null $user */
        $user = UserEntity::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("Пользователь #{$id} не найден.");
        }

        $dto = new PasswordSetRequestDto();
        $dto->load(Yii::$app->request->getBodyParams(), '');

        if (!$dto->validate()) {
            $errorString = json_encode($dto->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error("Ошибка валидации PasswordSetRequestDto для пользователя #{$id}: " . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Ошибка валидации пароля: ' . $errorString);
        }

        $user->password = $dto->password; // Хешируется в beforeSave()

        if (!$user->save(true, ['password_hash'])) {
            $errorString = json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error("Ошибка установки пароля для пользователя #{$id}: " . $errorString, __METHOD__);
            throw new ServerErrorHttpException('Не удалось установить новый пароль.');
        }

        $user->refresh();

        return [
            'success' => true,
            'message' => "Пароль для пользователя #{$user->id} успешно изменён",
            'user' => $user,
        ];
    }


    /**
     * Отключение учётной записи пользователя.
     *
     * Устанавливает статус 9 (STATUS_INACTIVE). Пользователь не сможет войти в систему,
     * но его данные сохраняются в БД. Нельзя отключить собственную учётную запись.
     *
     * @SWG\Post(
     *   tags={"admin / user controller"},
     *   path="/admin/user/{id}/disabled",
     *   summary="Отключить учётную запись",
     *   description="Устанавливает статус 'неактивен' (STATUS_INACTIVE = 9). Пользователь не сможет войти в систему, но данные сохраняются. Нельзя отключить свою учётную запись.",
     *   security={{"Bearer": {}}},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *
     *   @SWG\Response(response=200, description="Учётная запись отключена",
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="success", type="boolean", example=true),
     *           @SWG\Property(property="message", type="string", example="Учётная запись отключена"),
     *           @SWG\Property(property="user", ref="#/definitions/UserEntity")
     *       )
     *   ),
     *   @SWG\Response(response=404, description="Пользователь не найден"),
     *   @SWG\Response(response=403, description="Доступ запрещён (в т.ч. попытка отключить себя)"),
     *   @SWG\Response(response=422, description="Учётная запись уже отключена"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionDisabled(int $id)
    {
        $this->checkAccess('update');

        /** @var UserEntity|null $user */
        $user = UserEntity::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("Пользователь #{$id} не найден.");
        }

        // Нельзя отключить собственную учётную запись
        $currentUser = Yii::$app->user->identity;
        if ($currentUser && $currentUser->id === $user->id) {
            throw new \yii\web\ForbiddenHttpException('Нельзя отключить собственную учётную запись.');
        }

        if ($user->status === UserEntity::STATUS_INACTIVE) {
            throw new UnprocessableEntityHttpException("Учётная запись #{$id} уже отключена.");
        }

        $user->status = UserEntity::STATUS_INACTIVE;

        if (!$user->save(true, ['status'])) {
            $errorString = json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error("Ошибка отключения пользователя #{$id}: " . $errorString, __METHOD__);
            throw new ServerErrorHttpException('Не удалось отключить учётную запись.');
        }

        $user->refresh();

        return [
            'success' => true,
            'message' => "Учётная запись пользователя #{$user->id} отключена",
            'user' => $user,
        ];
    }

    /**
     * Назначение роли пользователю.
     * 
     * @SWG\Post(
     *   tags={"admin / user controller"},
     *   path="/admin/user/{id}/assign-role",
     *   summary="Назначить роль пользователю",
     *   description="Назначает пользователю роль по ID или по названию. Если роль уже назначена — операция идемпотентна.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *   @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="roleId", type="integer", description="ID роли (альтернатива полю role)"),
     *       @SWG\Property(property="role", type="string", description="Название роли (admin, manager, customer) — альтернатива roleId")
     *   )),
     *
     *   @SWG\Response(response=200, description="Роль успешно назначена", @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="success", type="boolean"),
     *       @SWG\Property(property="message", type="string"),
     *       @SWG\Property(property="user", ref="#/definitions/UserEntity")
     *   )),
     *   @SWG\Response(response=400, description="Не указан roleId или role"),
     *   @SWG\Response(response=404, description="Пользователь или роль не найдены"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionAssignRole(int $id)
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
     * Отзыв роли у пользователя.
     * 
     * @SWG\Delete(
     *   tags={"admin / user controller"},
     *   path="/admin/users/{id}/roles/{roleId}",
     *   summary="Отозвать роль у пользователя",
     *   description="Удаляет связь между пользователем и ролью.",
     *   security={{"Bearer": {}}},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID пользователя"),
     *   @SWG\Parameter(name="roleId", in="path", required=true, type="integer", description="ID роли"),
     *
     *   @SWG\Response(response=200, description="Роль успешно отозвана", @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="success", type="boolean"),
     *       @SWG\Property(property="message", type="string"),
     *       @SWG\Property(property="user", ref="#/definitions/UserEntity")
     *   )),
     *   @SWG\Response(response=404, description="Пользователь или роль не найдены"),
     *   @SWG\Response(response=422, description="У пользователя нет такой роли"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionRevokeRole(int $id, int $roleId)
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
}
