<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function tableName()
    {
        return 'user';
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password_hash'], 'required'],
            [['username', 'email'], 'string', 'max' => 128],
            [['email'], 'email'],
            [['username'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 20],
            [['name', 'surname', 'patronymic'], 'string', 'max' => 64],
            [['image'], 'string', 'max' => 255],
            [['date_of_birth'], 'safe'],
            [['address'], 'string'],
            [['created_at', 'updated_at', 'status'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'password_hash' => 'Пароль',
            'email' => 'Email',
            'phone' => 'Телефон',
            'address' => 'Адрес',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'date_of_birth' => 'Дата рождения',
            'image' => 'Фото',
            'created_at' => 'Дата регистрации',
            'updated_at' => 'Обновлено',
            'status' => 'Статус',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
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
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = \Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = \Yii::$app->security->generateRandomString();
    }

    public function isAdmin()
    {
        return \Yii::$app->user->identity && 
               \Yii::$app->db->createCommand(
                   'SELECT COUNT(*) FROM user_role WHERE user_id = :userId AND role_id = 1'
               )
               ->bindValue(':userId', \Yii::$app->user->id)
               ->queryScalar() > 0;
    }

    public function getRoles()
    {
        return $this->hasMany(Role::className(), ['id' => 'role_id'])
            ->viaTable('user_role', ['user_id' => 'id']);
    }

    public function getRequests()
    {
        return $this->hasMany(Request::className(), ['user_id' => 'id']);
    }

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'id']);
    }
}
