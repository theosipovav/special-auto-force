<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Категории" (Category)
 *
 * @property int $id Id
 * @property string $title Название (Title)
 * @property string|null $description Описание (Description)
 * @property int|null $image_id ID изображения
 *
 * @property ImageEntity|null $imageEntity
 * @property ProductCategory[] $productCategories
 * @property Product[] $products
 */
class Category extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%category}}';
    }

    public function rules()
    {
        return [
            [['title'], 'required', 'message' => 'Поле "Название" обязательно для заполнения'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['image_id'], 'integer'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => ImageEntity::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи',
            'title' => 'Название',
            'description' => 'Описание',
            'image_id' => 'Фото',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'description',
            'image' => function () {
                return $this->imageEntity ? $this->imageEntity->url : null;
            },
            'productsCount' => function () {
                return (int) $this->getProducts()->count();
            },
        ];
    }

    public function extraFields()
    {
        return [
            'imageEntity',
            'products',
            'productCategories',
        ];
    }

    public function getImageEntity()
    {
        return $this->hasOne(ImageEntity::class, ['id' => 'image_id']);
    }

    public function getProductCategories()
    {
        return $this->hasMany(ProductCategory::class, ['category_id' => 'id']);
    }

    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])
            ->via('productCategories');
    }
}
