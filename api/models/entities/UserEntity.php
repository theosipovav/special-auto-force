<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Модель сущности "Пользователь" (UserEntity)
 *
 * @SWG\Definition(definition="UserEntity", type="object", description="Модель пользователя (полное представление)", required={"id", "userName", "email", "phone", "name", "surname", "status", "datOfRegistration"},
 *     @SWG\Property(property="id", type="integer", description="ID пользователя", example=42),
 *     @SWG\Property(property="userName", type="string", description="Логин пользователя", example="ivanov_ii"),
 *     @SWG\Property(property="email", type="string", format="email", description="Адрес электронной почты", example="ivanov@example.com"),
 *     @SWG\Property(property="phone", type="string", description="Номер телефона", example="+7 (999) 123-45-67"),
 *     @SWG\Property(property="address", type="string", description="Адрес проживания", example="г. Москва, ул. Ленина, д. 10"),
 *     @SWG\Property(property="name", type="string", description="Имя", example="Иван"),
 *     @SWG\Property(property="surname", type="string", description="Фамилия", example="Иванов"),
 *     @SWG\Property(property="patronymic", type="string", description="Отчество", example="Иванович"),
 *     @SWG\Property(property="dateOfBirth", type="string", format="date", description="Дата рождения (YYYY-MM-DD)", example="1990-05-15"),
 *     @SWG\Property(property="datOfRegistration", type="string", format="date-time", description="Дата и время регистрации", example="2024-01-15T09:30:00+03:00"),
 *     @SWG\Property(property="fullName", type="string", description="Полное ФИО", example="Иванов Иван Иванович"),
 *     @SWG\Property(property="status", type="integer", enum={0, 9, 10}, description="Статус аккаунта: 0 — удалён, 9 — неактивен, 10 — активен", example=10),
 *     @SWG\Property(property="roles", type="array", description="Назначенные роли", @SWG\Items(ref="#/definitions/UserRoleEntity"))
 * )
 *
 *
 * @property int $id Идентификатор
 * @property string $username Логин
 * @property string $password_hash Хеш пароля
 * @property string $email Адрес электронной почты
 * @property string $phone Телефон
 * @property string|null $address Адрес
 * @property string $name Имя
 * @property string $surname Фамилия
 * @property string|null $patronymic Отчество
 * @property string|null $date_of_birth Дата рождения
 * @property string $date_of_registration Дата регистрации
 * @property string|null $auth_key Ключ авторизации
 * @property string|null $access_token Токен доступа REST API
 * @property int $status Статус аккаунта (10 = активен, 0 = заблокирован)
 *
 * @property UserRoleEntity[] $userRoles
 * @property RoleEntity[] $roles
 */
class UserEntity extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    /**
     * @var string|null Открытый пароль при создании или смене пароля
     */
    public $password;

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['username', 'email', 'phone', 'name', 'surname'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'unique'],
            ['email', 'email', 'message' => 'Некорректный формат адреса электронной почты'],
            [['date_of_birth', 'date_of_registration'], 'safe'],
            [['username', 'name', 'surname', 'patronymic'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 32],
            [['address'], 'string', 'max' => 255],
            ['password', 'string', 'min' => 6, 'message' => 'Пароль должен содержать минимум 6 символов'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин (UserName)',
            'password' => 'Пароль (Password)',
            'email' => 'Адрес электронной почты (Email)',
            'phone' => 'Телефон (Phone)',
            'address' => 'Адрес (Address)',
            'name' => 'Имя (Name)',
            'surname' => 'Фамилия (Surname)',
            'patronymic' => 'Отчество (Patronymic)',
            'date_of_birth' => 'Дата рождения (DateOfBirth)',
            'date_of_registration' => 'Дата регистрации (DatOfRegistration)',
            'status' => 'Статус аккаунта',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'userName' => 'username',
            'email',
            'phone',
            'address',
            'name',
            'surname',
            'patronymic',
            'dateOfBirth' => function () {
                return $this->date_of_birth;
            },
            'datOfRegistration' => function () {
                return $this->date_of_registration;
            },
            'fullName' => function () {
                return $this->getFullName();
            },
            'status',
            'roles' => function () {
                return array_map(function ($r) {
                    return [
                        'id' => $r->id,
                        'title' => $r->title,
                    ];
                }, $this->roles);
            },
        ];
    }

    public function extraFields()
    {
        return [
            'userRoles',
            'roles',
        ];
    }

    public function getFullName(): string
    {
        return trim("{$this->surname} {$this->name} {$this->patronymic}");
    }

    public function getUserRoles()
    {
        return $this->hasMany(UserRoleEntity::class, ['user_id' => 'id']);
    }

    public function getRoles()
    {
        return $this->hasMany(RoleEntity::class, ['id' => 'role_id'])
            ->via('userRoles');
    }

    /**
     * Проверка наличия роли у пользователя
     */
    public function hasRole(string $roleTitle): bool
    {
        foreach ($this->roles as $role) {
            if (strcasecmp($role->title, $roleTitle) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Назначение роли пользователю
     */
    public function assignRole(int $roleId): bool
    {
        $existing = UserRoleEntity::findOne(['user_id' => $this->id, 'role_id' => $roleId]);
        if (!$existing) {
            $ur = new UserRoleEntity();
            $ur->user_id = $this->id;
            $ur->role_id = $roleId;
            return $ur->save();
        }
        return true;
    }

    /**
     * Отзыв роли у пользователя
     */
    public function revokeRole(int $roleId): bool
    {
        $existing = UserRoleEntity::findOne(['user_id' => $this->id, 'role_id' => $roleId]);
        if ($existing) {
            return (bool) $existing->delete();
        }
        return true;
    }

    // ==========================================
    // IdentityInterface implementation & Auth
    // ==========================================

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        // 1. Check direct token in db
        $user = static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
        if ($user) {
            return $user;
        }

        // 2. Decode JWT Bearer Token
        try {
            $secretKey = Yii::$app->params['jwtSecretKey'] ?? 'SpetsAvtoSila_Secure_JWT_Secret_Key_2026_Yii2_RestApi';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            if (isset($decoded->sub)) {
                return static::findOne(['id' => $decoded->sub, 'status' => self::STATUS_ACTIVE]);
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public static function findByUsername(string $username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail(string $email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generateAccessToken(): string
    {
        $secretKey = Yii::$app->params['jwtSecretKey'] ?? 'SpetsAvtoSila_Secure_JWT_Secret_Key_2026_Yii2_RestApi';
        $expire = Yii::$app->params['jwtExpire'] ?? (86400 * 7);

        $payload = [
            'iss' => 'specavtosila-api',
            'aud' => 'specavtosila-client',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + $expire,
            'sub' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => array_map(function ($r) {
                return $r->title;
            }, $this->roles),
        ];

        $token = JWT::encode($payload, $secretKey, 'HS256');
        $this->access_token = $token;
        $this->save(false, ['access_token']);

        return $token;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->generateAuthKey();
                if (empty($this->date_of_registration)) {
                    $this->date_of_registration = date('Y-m-d H:i:s');
                }
            }
            if (!empty($this->password)) {
                $this->setPassword($this->password);
            }
            return true;
        }
        return false;
    }
}
