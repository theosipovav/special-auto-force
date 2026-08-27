<?php

namespace app\models\dtos\request;

use Yii;
use yii\base\Model;
use app\models\entities\UserEntity;

/**
 * Форма авторизации.
 *
 * @SWG\Definition(
 *     definition="LoginForm",
 *     required={"username", "password"},
 *
 *     @SWG\Property(property="username", type="string", description="Логин или E-mail", example="user@example.com"),
 *     @SWG\Property(property="password", type="string", format="password", description="Пароль", example="password123")
 * )
 */
class LoginForm extends Model
{
    public string $username;
    public string $password;

    private ?UserEntity $_user = null;

    public function rules()
    {
        return [
            [['username', 'password'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логин или E-mail',
            'password' => 'Пароль',
        ];
    }

      /**
     * Валидация пароля и статуса пользователя.
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            /** @var UserEntity $user */
            $user = $this->getUser();
            
            if (!$user) {
                $this->addError('username', 'Пользователь с таким логином или email не найден.');
            } elseif (!$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный пароль.');
            } elseif ($user->status === UserEntity::STATUS_INACTIVE) {
                $this->addError('username', 'Учетная запись отключена. Обратитесь к администратору.');
            } elseif ($user->status === UserEntity::STATUS_DELETED) {
                $this->addError('username', 'Учетная запись удалена.');
            }
        }
    }

    public function getUser()
    {
        if ($this->_user === null || $this->_user === false) {

            $this->_user = UserEntity::find()
                ->where(['username' => $this->username])
                ->orWhere(['email' => $this->username])
                ->one();
        }
        return $this->_user;
    }

    public function login(): ?array
    {
        if ($this->validate()) {

            /** @var UserEntity $user */
            $user = $this->getUser();
            $token = $user->generateAccessToken();

            return [
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => Yii::$app->params['jwtExpire'] ?? (86400 * 7),
                'user' => $user->toArray(),
            ];
        }

        return null;
    }
}
