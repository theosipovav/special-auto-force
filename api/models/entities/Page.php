<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Страница" (Page)
 *
 * @property int $id Id
 * @property string $title Заголовок (Title)
 * @property string $url Адрес (Url)
 * @property string|null $description Описание (Description)
 * @property string $dateCreate Дата создания (DateCreate)
 */
class Page extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%page}}';
    }

    public function rules()
    {
        return [
            [['title', 'url'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['description'], 'string'],
            [['title', 'url'], 'string', 'max' => 255],
            [['url'], 'unique', 'message' => 'Страница с таким URL уже существует'],
            [['dateCreate'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'url' => 'Адрес',
            'description' => 'Описание',
            'dateCreate' => 'Дата создания',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'url',
            'description',
            'dateCreate',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->dateCreate = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }
}
