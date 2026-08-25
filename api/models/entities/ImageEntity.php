<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Изображение" (Image)
 *
 * @property int $id Id
 * @property string $title Название картинки
 * @property string $path Относительный путь в файловой системе
 * @property string $url URL для открытия
 */
class ImageEntity extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%image}}';
    }

    public function rules()
    {
        return [
            [['title', 'path', 'url'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['title', 'path', 'url'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название картинки',
            'path' => 'Путь к файлу',
            'url' => 'URL для открытия',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'path',
            'url',
        ];
    }

    public function extraFields()
    {
        return [];
    }
}
