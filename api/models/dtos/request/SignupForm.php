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
 *     required={"userтame", "password", "passwordRepeat", "email", "phone", "name", "surname"},
 *
 *     @SWG\Property(property="userтame", type="string", maxLength=64, description="Логин пользователя", example="newuser"),
 *     @SWG\Property(property="password", type="string", format="password", minLength=6, description="Пароль (минимум 6 символов)", example="password123"),
 *     @SWG\Property(property="passwordRepeat", type="string", format="password", description="Повтор пароля", example="password123"),
 *     @SWG\Property(property="email", type="string", format="email", description="Адрес электронной почты", example="user@example.com"),
 *     @SWG\Property(property="phone", type="string", maxLength=32, description="Номер телефона", example="+79991234567"),
 *     @SWG\Property(property="address", type="string", maxLength=255, description="Адрес пользователя", example="г. Москва, ул. Ленина 1"),
 *     @SWG\Property(property="name", type="string", maxLength=64, description="Имя", example="Иван"),
 *     @SWG\Property(property="surname", type="string", maxLength=64, description="Фамилия", example="Иванов"),
 *     @SWG\Property(property="patronymic", type="string", maxLength=64, description="Отчество", example="Иванович"),
 *     @SWG\Property(property="dateOfBirth", type="string", format="date", description="Дата рождения (YYYY-MM-DD)", example="1990-01-01")
 * )
 */
class SignupForm extends Model
{
    /** Логин пользователя */
    public string $userтame;

    /** Пароль */
    public string $password;

    /** Пароль (повтор) */
    public string $passwordRepeat;

    /** Адрес электронной почты */
    public ?string $email = null;

    /** Номер телефона */
    public ?string $phone = null;

    /** Адрес пользователя */
    public ?string $address = null;

    /** Имя */
    public string $name;

    /** Фамилия */
    public string $surname;

    /** Отчество */
    public ?string $patronymic = null;

    /** Дата рождения */
    public ?string $dateOfBirth = null;


    public function rules()
    {
        return [
            [['userтame', 'password', 'passwordRepeat', 'email', 'phone', 'name', 'surname'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['userтame', 'email'], 'trim'],
            ['userтame', 'unique', 'targetClass' => UserEntity::class, 'targetAttribute' => 'username', 'message' => 'Этот логин уже занят'],
            ['email', 'email', 'message' => 'Некорректный формат E-mail'],
            ['email', 'unique', 'targetClass' => UserEntity::class, 'targetAttribute' => 'email', 'message' => 'Этот E-mail уже зарегистрирован'],
            ['password', 'string', 'min' => 6, 'message' => 'Минимальная длина пароля - 6 символов'],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
            [['userтame', 'name', 'surname', 'patronymic'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 32],
            [['address'], 'string', 'max' => 255],
            [['dateOfBirth'], 'date', 'format' => 'php:Y-m-d', 'message' => 'Некорректный формат даты (ожидается YYYY-MM-DD)'],
        ];
    }


    public function signup(): ?UserEntity
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new UserEntity();
        $user->username = $this->userтame;
        $user->email = $this->email;
        $user->phone = $this->phone;
        $user->name = $this->name;
        $user->surname = $this->surname;
        $user->patronymic = $this->patronymic;
        $user->address = $this->address;
        $user->date_of_birth = $this->dateOfBirth;
        $user->password = $this->password; // Хешируется в beforeSave()
        $user->status = UserEntity::STATUS_ACTIVE;
        $user->date_of_registration = date('Y-m-d H:i:s');

        if ($user->save()) {
            // Автоматическое назначение роли "customer"
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
