<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Product model
 */
class Product extends ActiveRecord
{
    public static function tableName()
    {
        return 'product';
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['short_description', 'info'], 'string'],
            [['long_description'], 'string'],
            [['created_at', 'updated_at', 'views', 'orders_count'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'short_description' => 'Краткое описание',
            'long_description' => 'Подробное описание',
            'info' => 'Дополнительная информация',
            'views' => 'Просмотры',
            'orders_count' => 'Количество заказов',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
            }
            $this->updated_at = time();
            return true;
        }
        return false;
    }

    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])
            ->viaTable('product_category', ['product_id' => 'id']);
    }

    public function getImages()
    {
        return $this->hasMany(ProductImage::className(), ['product_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'is_main' => SORT_DESC]);
    }

    public function getMainImage()
    {
        return $this->hasOne(ProductImage::className(), ['product_id' => 'id'])
            ->where(['is_main' => 1]);
    }

    public function getCategoryTitles()
    {
        return implode(', ', array_column($this->categories, 'title'));
    }

    public function incrementViews()
    {
        $this->views++;
        $this->save(false);
    }
}
