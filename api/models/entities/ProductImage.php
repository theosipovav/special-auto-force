<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Связь между товарами и фотографиями" (ProductImage)
 *
 * @property int $product_id Идентификатор продукта
 * @property int $image_id Идентификатор изображения
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
            [['product_id', 'image_id', 'title'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['product_id', 'image_id'], 'integer'],
            [['is_main'], 'boolean'],
            [['title'], 'string', 'max' => 255],
            [['product_id', 'image_id'], 'unique', 'targetAttribute' => ['product_id', 'image_id'], 'message' => 'Такая связка продукта и изображения уже существует'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => ImageEntity::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'product_id' => 'Идентификатор продукта',
            'image_id' => 'Идентификатор изображения',
            'is_main' => 'Главное фото',
            'title' => 'Название',
        ];
    }

    public function fields()
    {
        return [
            'product_id',
            'image_id',
            'is_main',
            'title',
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

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ['product_id', 'image_id'];
    }
}
