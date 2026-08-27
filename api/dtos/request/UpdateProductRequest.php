<?php

namespace app\dtos\request;

use Yii;
use yii\base\Model;

/**
 * DTO для обновления продукции.
 *
 * @SWG\Definition(
 *     definition="UpdateProductRequest",
 *     required={"title", "shortDescription", "longDescription"},
 *
 *     @SWG\Property(property="title", type="string", maxLength=255, description="Название товара"),
 *     @SWG\Property(property="article", type="string", maxLength=255, description="Артикул товара"),
 *     @SWG\Property(property="shortDescription", type="string", description="Краткое описание товара"),
 *     @SWG\Property(property="longDescription", type="string", description="Подробное описание товара"),
 *     @SWG\Property(property="info", type="string", description="Дополнительная информация"),
 *     @SWG\Property(property="price", type="number", format="float", minimum=0, description="Цена товара"),
 *     @SWG\Property(property="inStock", type="boolean", description="Наличие товара на складе"),
 *     @SWG\Property(property="manufacturer", type="string", maxLength=255, description="Производитель"),
 *     @SWG\Property(property="country", type="string", maxLength=255, description="Страна производства"),
 * )
 */
class UpdateProductRequest extends Model
{
    public string $title;
    public string $article = '';
    public string $shortDescription;
    public string $longDescription;
    public string $info = '';
    public ?float $price = null;
    public bool $inStock = false;
    public string $manufacturer = '';
    public string $country = '';
    
    public function rules()
    {
        return [
            [['title', 'shortDescription', 'longDescription'], 'required'],
            [['title', 'article', 'manufacturer', 'country'], 'string', 'max' => 255],
            [['shortDescription', 'longDescription', 'info'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['inStock'], 'boolean'],
        ];
    }
}