<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель связи "Связь между товарами и категориями"
 *
 * @property int $category_id CategoryId
 * @property int $product_id ProductId
 *
 * @property CategoryEntity $category
 * @property ProductEntity $product
 */
class ProductCategoryEntity extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%product_category}}';
    }

    public static function primaryKey()
    {
        return ['category_id', 'product_id'];
    }

    public function rules()
    {
        return [
            [['category_id', 'product_id'], 'required'],
            [['category_id', 'product_id'], 'integer'],
            [['category_id', 'product_id'], 'unique', 'targetAttribute' => ['category_id', 'product_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => CategoryEntity::class, 'targetAttribute' => ['category_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductEntity::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'category_id' => 'Идентификатор категории',
            'product_id' => 'Идентификатор товара',
        ];
    }

    public function fields()
    {
        return [
            'categoryId' => 'category_id',
            'productId' => 'product_id',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(CategoryEntity::class, ['id' => 'category_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(ProductEntity::class, ['id' => 'product_id']);
    }
}
