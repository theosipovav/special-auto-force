<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Товар" (Product)
 *
 * @property int $id Id
 * @property string $title Название (Title)
 * @property string $short_description Краткое описание (ShortDescription)
 * @property string $long_description Подробное описание (LongDescription)
 * @property string|null $info Дополнительная информация (Info)
 * @property string|null $article Артикул / Заводской код
 * @property float|null $price Цена
 * @property int $in_stock Наличие на складе (1 - в наличии, 0 - под заказ)
 * @property string|null $manufacturer Производитель
 * @property string|null $country Страна производства
 * @property string $created_at Дата создания
 *
 * @property ProductCategory[] $productCategories
 * @property Category[] $categories
 * @property ProductImage[] $productImages
 */
class Product extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%product}}';
    }

    public function rules()
    {
        return [
            [['title', 'short_description', 'long_description'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['short_description', 'long_description', 'info'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['in_stock'], 'integer'],
            [['title', 'manufacturer'], 'string', 'max' => 255],
            [['article', 'country'], 'string', 'max' => 64],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название товара',
            'short_description' => 'Краткое описание',
            'long_description' => 'Подробное описание',
            'info' => 'Дополнительная информация',
            'article' => 'Артикул',
            'price' => 'Цена',
            'in_stock' => 'В наличии',
            'manufacturer' => 'Производитель',
            'country' => 'Страна',
            'created_at' => 'Дата добавления',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'shortDescription' => 'short_description',
            'longDescription' => 'long_description',
            'info',
            'parsedInfo' => function () {
                return $this->getParsedInfo();
            },
            'article',
            'price',
            'inStock' => function () {
                return (bool) $this->in_stock;
            },
            'manufacturer',
            'country',
            'categories' => function () {
                return array_map(function ($c) {
                    return [
                        'id' => $c->id,
                        'title' => $c->title,
                    ];
                }, $this->categories);
            },
            'images' => function () {
                return array_map(function ($img) {
                    return [
                        'product_id' => $img->product_id,
                        'image_id' => $img->image_id,
                        'is_main' => $img->is_main,
                        'title' => $img->title,
                        'url' => $img->imageEntity ? $img->imageEntity->url : null,
                    ];
                }, $this->productImages);
            },
            'createdAt' => 'created_at',
        ];
    }

    public function extraFields()
    {
        return [
            'productCategories',
            'categories',
            'productImages',
        ];
    }

    public function getProductCategories()
    {
        return $this->hasMany(ProductCategory::class, ['product_id' => 'id']);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->via('productCategories');
    }

    public function getProductImages()
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id']);
    }

    /**
     * Преобразование дополнительной информации в структурированный массив характеристик
     */
    public function getParsedInfo(): array
    {
        if (empty($this->info)) {
            return [];
        }

        $decoded = json_decode($this->info, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Парсинг формата "Ключ: Значение" построчно
        $result = [];
        $lines = explode("\n", $this->info);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (strpos($line, ':') !== false) {
                list($key, $val) = explode(':', $line, 2);
                $result[trim($key)] = trim($val);
            } else {
                $result['Информация'] = $line;
            }
        }
        return $result;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord && empty($this->created_at)) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }
}
