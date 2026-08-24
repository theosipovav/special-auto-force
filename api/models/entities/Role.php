<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Роль" (Role)
 *
 * @property int $id Id
 * @property string $title Название (Title)
 *
 * @property UserRole[] $userRoles
 * @property User[] $users
 */
class Role extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%role}}';
    }

    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "Название" обязательно для заполнения'],
            [['title'], 'string', 'max' => 64],
            [['title'], 'unique', 'message' => 'Роль с таким названием уже существует'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название роли',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
        ];
    }

    public function extraFields()
    {
        return [
            'users',
            'usersCount' => function () {
                return $this->getUsers()->count();
            },
        ];
    }

    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, ['role_id' => 'id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->via('userRoles');
    }
}
