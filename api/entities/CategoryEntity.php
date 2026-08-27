<?php

namespace app\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Категории"
 *
 * @property int $id Id
 * @property string $title Название (Title)
 * @property string|null $description Описание (Description)
 * @property int|null $image_id ID изображения
 *
 * @property ImageEntity|null $imageEntity
 * @property ProductCategoryEntity[] $productCategories
 * @property ProductEntity[] $products
 */
class CategoryEntity extends ActiveRecord
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
        return $this->hasMany(ProductCategoryEntity::class, ['category_id' => 'id']);
    }

    public function getProducts()
    {
        return $this->hasMany(ProductEntity::class, ['id' => 'product_id'])
            ->via('productCategories');
    }
}
