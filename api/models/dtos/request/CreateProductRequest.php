<?php

namespace app\models\dtos\request;

use Yii;
use yii\base\Model;

/**
 * DTO для создания продукции.
 *
 * @SWG\Definition(
 *     definition="CreateProductRequest",
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
 *     @SWG\Property(property="categoryIds", type="array", description="Идентификаторы категорий", @SWG\Items(type="integer")),
 *     @SWG\Property(property="images", type="array", description="Массив изображений товара (одно из них должно быть с isMain=true)", @SWG\Items(ref="#/definitions/FormFileImageDto"))
 * )
 */
class CreateProductRequest extends Model
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
    
    public array $categoryIds = [];

    /**
     * Массив объектов FormFileImageDto
     * @var FormFileImageDto[]
     */
    public array $images = [];

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'article', 'manufacturer', 'country'], 'string', 'max' => 255],
            [['shortDescription', 'longDescription', 'info'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['inStock'], 'boolean'],
            [['categoryIds'], 'each', 'rule' => ['integer']],
            [['images'], 'validateImages'],
        ];
    }

    /**
     * Кастомный валидатор для массива изображений
     */
    public function validateImages($attribute, $params)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Поле images должно быть массивом.');
            return;
        }

        $mainImagesCount = 0;
        
        foreach ($this->$attribute as $index => $imageDto) {
            // Если пришел ассоциативный массив вместо объекта, преобразуем его
            if (is_array($imageDto)) {
                $dto = new FormFileImageDto();
                $dto->load($imageDto, '');
                $this->{$attribute}[$index] = $dto;
                $imageDto = $dto;
            }

            if (!$imageDto instanceof FormFileImageDto) {
                $this->addError($attribute, "Элемент images[$index] имеет неверный формат.");
                continue;
            }

            // Валидируем сам DTO
            if (!$imageDto->validate()) {
                foreach ($imageDto->getErrors() as $attr => $errors) {
                    foreach ($errors as $error) {
                        $this->addError($attribute, "Ошибка в images[$index].$attr: $error");
                    }
                }
            }

            if (!empty($imageDto->isMain)) {
                $mainImagesCount++;
            }
        }

        if ($mainImagesCount > 1) {
            $this->addError($attribute, 'Может быть только одно главное изображение (isMain = true).');
        }
    }

}