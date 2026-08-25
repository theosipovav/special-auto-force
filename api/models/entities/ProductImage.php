<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Связь между товарами и фотографиями" (ProductImage)
 *
 * @property int $product_id Идентификатор продукиа
 * @property int|null $image_id Идентификатор изображения
 * @property bool $is_main Главное фото
 * @property string $title Название
 *
 * @property Product $product
 * @property ImageEntity|null $imageEntity
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
            [['product_id', 'title'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['product_id', 'image_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => ImageEntity::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Идентификатор товара',
            'title' => 'Название фото',
            'image_id' => 'Изображение',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'productId' => 'product_id',
            'title',
            'image' => function () {
                return $this->imageEntity ? $this->imageEntity->url : null;
            },
        ];
    }

    public function extraFields()
    {
        return [
            'imageEntity',
            'product',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getImageEntity()
    {
        return $this->hasOne(ImageEntity::class, ['id' => 'image_id']);
    }
}
