<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель связи "Связь между пользователями и ролями" (UserRole)
 * У каждого пользователя может быть несколько ролей.
 *
 * @property int $user_id UserId
 * @property int $role_id RoleId
 *
 * @property User $user
 * @property Role $role
 */
class UserRole extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_role}}';
    }

    public static function primaryKey()
    {
        return ['user_id', 'role_id'];
    }

    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id'], 'integer'],
            [['user_id', 'role_id'], 'unique', 'targetAttribute' => ['user_id', 'role_id'], 'message' => 'Эта роль уже назначена пользователю'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'Идентификатор пользователя',
            'role_id' => 'Идентификатор роли',
        ];
    }

    public function fields()
    {
        return [
            'userId' => 'user_id',
            'roleId' => 'role_id',
        ];
    }

    public function extraFields()
    {
        return [
            'user',
            'role',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }
}
