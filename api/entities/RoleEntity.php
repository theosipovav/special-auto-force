<?php

namespace app\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Роль" (RoleEntity).
 *
 * @SWG\Definition(
 *     definition="RoleEntity",
 *     required={"id", "title"},
 *
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Идентификатор роли"
 *     ),
 *     @SWG\Property(
 *         property="title",
 *         type="string",
 *         maxLength=64,
 *         description="Название роли"
 *     ),
 *     @SWG\Property(
 *         property="users",
 *         type="array",
 *         description="Пользователи, которым назначена роль. Возвращается при expand=users.",
 *         @SWG\Items(
 *             ref="#/definitions/UserEntity"
 *         )
 *     ),
 *     @SWG\Property(
 *         property="usersCount",
 *         type="integer",
 *         format="int32",
 *         description="Количество пользователей с данной ролью. Возвращается при expand=usersCount."
 *     )
 * )
 */
class RoleEntity extends ActiveRecord
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
        return $this->hasMany(UserRoleEntity::class, ['role_id' => 'id']);
    }

    public function getUsers()
    {
        return $this->hasMany(UserEntity::class, ['id' => 'user_id'])
            ->via('userRoles');
    }
}
