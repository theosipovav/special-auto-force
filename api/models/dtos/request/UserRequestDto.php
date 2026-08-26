<?php

namespace app\models\dtos\request;

use yii\base\Model;

/**
 * DTO для создания и обновления пользователя
 *
 * @SWG\Definition(
 *     definition="UserRequestDto",
 *     type="object",
 *     description="DTO для создания и обновления пользователя",
 *     required={"userName", "password", "passwordRepeat", "name", "surname"},
 *
 *     @SWG\Property(property="userName", type="string", description="Логин пользователя", example="ivanov_ii"),
 *     @SWG\Property(property="password", type="string", description="Пароль (обязателен при создании, опционален при обновлении)", example="secret123"),
 *     @SWG\Property(property="passwordRepeat", type="string", description="Повтор пароля", example="secret123"),
 *     @SWG\Property(property="email", type="string", format="email", description="Адрес электронной почты", example="ivanov@example.com"),
 *     @SWG\Property(property="phone", type="string", description="Номер телефона", example="+7 (999) 123-45-67"),
 *     @SWG\Property(property="address", type="string", description="Адрес проживания", example="г. Москва, ул. Ленина, д. 10"),
 *     @SWG\Property(property="name", type="string", description="Имя", example="Иван"),
 *     @SWG\Property(property="surname", type="string", description="Фамилия", example="Иванов"),
 *     @SWG\Property(property="patronymic", type="string", description="Отчество", example="Иванович"),
 *     @SWG\Property(property="dateOfBirth", type="string", format="date", description="Дата рождения (YYYY-MM-DD)", example="1990-05-15"),
 *     @SWG\Property(property="status", type="integer", enum={0, 9, 10}, description="Статус аккаунта (только при обновлении)")
 * )
 */
class UserRequestDto extends Model
{
    /** Сценарий создания */
    const SCENARIO_CREATE = 'create';

    /** Сценарий обновления */
    const SCENARIO_UPDATE = 'update';

    /** Логин пользователя */
    public ?string $userName = null;

    /** Пароль */
    public ?string $password = null;

    /** Пароль (повторный) */
    public ?string $passwordRepeat = null;

    /** Email */
    public ?string $email = null;

    /** Телефон */
    public ?string $phone = null;

    /** Адрес */
    public ?string $address = null;

    /** Имя */
    public ?string $name = null;

    /** Фамилия */
    public ?string $surname = null;

    /** Отчество */
    public ?string $patronymic = null;

    /** Дата рождения */
    public ?string $dateOfBirth = null;

    /** Статус (только для update) */
    public ?int $status = null;


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['userName', 'password', 'passwordRepeat', 'email', 'phone', 'address', 'name', 'surname', 'patronymic', 'dateOfBirth'];
        $scenarios[self::SCENARIO_UPDATE] = ['userName', 'password', 'passwordRepeat', 'email', 'phone', 'address', 'name', 'surname', 'patronymic', 'dateOfBirth', 'status'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            // Обязательные поля при создании
            [['userName', 'password', 'passwordRepeat', 'name', 'surname'], 'required', 'on' => self::SCENARIO_CREATE],
            
            // Обязательные поля при обновлении (пароль опционален)
            [['userName', 'name', 'surname'], 'required', 'on' => self::SCENARIO_UPDATE],
            
            // Пароль при обновлении — если передан, то обязательны оба поля
            [['passwordRepeat'], 'required', 'when' => function ($model) {
                return !empty($model->password);
            }, 'whenClient' => "function (attribute, value) { return $('#userrequestdto-password').val() !== ''; }"],
            
            [['userName', 'email'], 'trim'],
            ['userName', 'string', 'max' => 64],
            ['email', 'email', 'message' => 'Некорректный формат адреса электронной почты'],
            [['dateOfBirth'], 'date', 'format' => 'php:Y-m-d'],
            [['userName', 'name', 'surname', 'patronymic'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 32],
            [['address'], 'string', 'max' => 255],
            ['password', 'string', 'min' => 6, 'message' => 'Пароль должен содержать минимум 6 символов', 'skipOnEmpty' => true],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают', 'skipOnEmpty' => true],
            ['status', 'integer'],
            ['status', 'in', 'range' => [0, 9, 10]],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'userName' => 'Логин',
            'password' => 'Пароль',
            'passwordRepeat' => 'Повтор пароля',
            'email' => 'Электронная почта',
            'phone' => 'Телефон',
            'address' => 'Адрес',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'dateOfBirth' => 'Дата рождения',
            'status' => 'Статус аккаунта',
        ];
    }
}
