<?php

namespace app\models\dtos\request;

use Yii;
use yii\base\Model;
use app\models\entities\UserEntity;
use app\models\entities\RoleEntity;

/**
 * Форма регистрации пользователя.
 *
 * @SWG\Definition(
 *     definition="SignupForm",
 *     required={"userName", "password", "email", "phone", "name", "surname"},
 *
 *     @SWG\Property(property="userName", type="string", maxLength=64, description="Логин пользователя"),
 *     @SWG\Property(property="password", type="string", format="password", minLength=6, description="Пароль"),
 *     @SWG\Property(property="email", type="string", format="email", description="Адрес электронной почты"),
 *     @SWG\Property(property="phone", type="string", maxLength=32, description="Номер телефона"),
 *     @SWG\Property(property="address", type="string", maxLength=255, description="Адрес пользователя"),
 *     @SWG\Property(property="name", type="string", maxLength=64, description="Имя"),
 *     @SWG\Property(property="surname", type="string", maxLength=64, description="Фамилия"),
 *     @SWG\Property(property="patronymic", type="string", maxLength=64, description="Отчество"),
 *     @SWG\Property(property="dateOfBirth", type="string", format="date", description="Дата рождения"),
 * )
 */
class SignupForm extends Model
{
    /** Логин пользователя */
    public string $userName;

    /** Пароль */
    public string $password;

    /** Пароль (повтор) */
    public string $passwordRepeat;
    
    /** Адрес электронной почты */
    public ?string $email;
    
    /** Номер телефона */
    public ?string $phone;
    
    /** Адрес пользователя */
    public ?string $address;
    
    /** Имя */
    public string $name;
    
    /** Фамилия */
    public string$surname;
    
    /** Отчество */
    public ?string $patronymic;
    
    /** Дата рождения */
    public ?string $dateOfBirth;
    

    public function rules()
    {
        return [
            [['userName', 'password', 'email', 'phone', 'name', 'surname'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['userName', 'email'], 'trim'],
            ['userName', 'unique', 'targetClass' => UserEntity::class, 'targetAttribute' => 'username', 'message' => 'Этот логин уже занят'],
            ['email', 'email', 'message' => 'Некорректный формат E-mail'],
            ['email', 'unique', 'targetClass' => UserEntity::class, 'targetAttribute' => 'email', 'message' => 'Этот E-mail уже зарегистрирован'],
            ['password', 'string', 'min' => 6, 'message' => 'Минимальная длина пароля - 6 символов'],
            [['userName', 'name', 'surname', 'patronymic'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 32],
            [['dateOfBirth'], 'safe'],
        ];
    }

    public function signup(): ?UserEntity
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new UserEntity();
        $user->username = $this->userName;
        $user->email = $this->email;
        $user->phone = $this->phone;
        $user->name = $this->name;
        $user->surname = $this->surname;
        $user->patronymic = $this->patronymic;
        $user->address = $this->address;
        $user->date_of_birth = $this->dateOfBirth;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = UserEntity::STATUS_ACTIVE;
        $user->date_of_registration = date('Y-m-d H:i:s');

        if ($user->save()) {
            // Назначение роли "customer" (клиент) по умолчанию
            $customerRole = RoleEntity::findOne(['title' => 'customer']);
            if (!$customerRole) {
                $customerRole = RoleEntity::findOne(['title' => 'Клиент']);
            }
            if ($customerRole) {
                $user->assignRole($customerRole->id);
            }

            return $user;
        }

        return null;
    }
}
