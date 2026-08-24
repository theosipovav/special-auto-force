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
 * @property string|null $image Фото (Image) ссылка
 *
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
            [['title', 'image'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи',
            'title' => 'Название',
            'description' => 'Описание',
            'image' => 'Фото',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'description',
            'image',
            'productsCount' => function () {
                return (int) $this->getProducts()->count();
            },
        ];
    }

    public function extraFields()
    {
        return [
            'products',
            'productCategories',
        ];
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
