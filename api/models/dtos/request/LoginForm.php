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
 *     @SWG\Property(
 *         property="username",
 *         type="string",
 *         description="Логин или E-mail"
 *     ),
 *     @SWG\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Пароль"
 *     )
 * )
 */
class LoginForm extends Model
{
    public $username;
    public $password;

    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            // ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логин или E-mail',
            'password' => 'Пароль',
        ];
    }

    
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            /** @var UserEntity $user */
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный логин или пароль.');
            }
        }
    }

    public function getUser()
    {
        if ($this->_user === false) {
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
