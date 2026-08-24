<?php

namespace app\models\dtos\request;

use Yii;
use yii\base\Model;
use app\models\entities\User;
use app\models\entities\Role;

/**
 * Форма регистрации пользователя в REST API
 */
class SignupForm extends Model
{
    public $userName;
    public $password;
    public $email;
    public $phone;
    public $address;
    public $name;
    public $surname;
    public $patronymic;
    public $dateOfBirth;
    public $image;

    public function rules()
    {
        return [
            [['userName', 'password', 'email', 'phone', 'name', 'surname'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['userName', 'email'], 'trim'],
            ['userName', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username', 'message' => 'Этот логин уже занят'],
            ['email', 'email', 'message' => 'Некорректный формат E-mail'],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email', 'message' => 'Этот E-mail уже зарегистрирован'],
            ['password', 'string', 'min' => 6, 'message' => 'Минимальная длина пароля - 6 символов'],
            [['userName', 'name', 'surname', 'patronymic'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 32],
            [['address', 'image'], 'string', 'max' => 255],
            [['dateOfBirth'], 'safe'],
        ];
    }

    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->userName;
        $user->email = $this->email;
        $user->phone = $this->phone;
        $user->name = $this->name;
        $user->surname = $this->surname;
        $user->patronymic = $this->patronymic;
        $user->address = $this->address;
        $user->date_of_birth = $this->dateOfBirth;
        $user->image = $this->image ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=400';
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->date_of_registration = date('Y-m-d H:i:s');

        if ($user->save()) {
            // Назначение роли "customer" (клиент) по умолчанию
            $customerRole = Role::findOne(['title' => 'customer']);
            if (!$customerRole) {
                $customerRole = Role::findOne(['title' => 'Клиент']);
            }
            if ($customerRole) {
                $user->assignRole($customerRole->id);
            }

            return $user;
        }

        return null;
    }
}
