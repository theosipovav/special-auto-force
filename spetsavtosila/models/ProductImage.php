<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * ProductImage model
 */
class ProductImage extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_image';
    }

    public function rules()
    {
        return [
            [['product_id', 'image'], 'required'],
            [['product_id', 'sort_order', 'is_main'], 'integer'],
            [['title'], 'string', 'max' => 128],
            [['image'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Товар',
            'title' => 'Название',
            'image' => 'Фото',
            'is_main' => 'Главное фото',
            'sort_order' => 'Порядок сортировки',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
