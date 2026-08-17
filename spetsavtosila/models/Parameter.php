<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Parameter model
 */
class Parameter extends ActiveRecord
{
    public static function tableName()
    {
        return 'parameter';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 128],
            [['value'], 'string'],
            [['type'], 'string', 'max' => 32],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'value' => 'Значение',
            'type' => 'Тип',
        ];
    }

    public static function getValue($title, $default = '')
    {
        $param = self::findOne(['title' => $title]);
        return $param ? $param->value : $default;
    }
}
