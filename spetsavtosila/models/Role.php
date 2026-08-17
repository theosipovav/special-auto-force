<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Role model
 */
class Role extends ActiveRecord
{
    public static function tableName()
    {
        return 'role';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 64],
            [['description'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'description' => 'Описание',
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('user_role', ['role_id' => 'id']);
    }
}
