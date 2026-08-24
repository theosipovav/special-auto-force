<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Связь между товарами и фотографиями" (ProductImage)
 *
 * @property int $id Id
 * @property int $product_id ProductId
 * @property string $title Название (Title)
 * @property string $image Фото (Image) ссылка
 *
 * @property Product $product
 */
class ProductImage extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%product_image}}';
    }

    public function rules()
    {
        return [
            [['product_id', 'title', 'image'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['product_id'], 'integer'],
            [['title', 'image'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Идентификатор товара',
            'title' => 'Название фото',
            'image' => 'Ссылка на фото',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'productId' => 'product_id',
            'title',
            'image',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
