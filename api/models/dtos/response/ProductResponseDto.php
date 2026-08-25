<?php

namespace app\models\dto\response;

/**
 * DTO для ответа с данными товара
 */
class ProductResponseDto
{
    public int $id;
    public string $title;
    public string $short_description;
    public string $long_description;
    public ?string $info;
    public ?string $article;
    public ?float $price;
    public int $in_stock;
    public array $images;
    public ?string $manufacturer;
    public ?string $country;
    public string $created_at;

    /**
     * @param int $id Идентификатор
     * @param string $title Название
     * @param string $short_description Краткое описание
     * @param string $long_description Подробное описание
     * @param string|null $info Дополнительная информация
     * @param string|null $article Артикул / Заводской код
     * @param float|null $price Цена
     * @param int $in_stock Наличие на складе (1 - в наличии, 0 - под заказ)
     * @param array $images Коллекция связанных ProductImageResponseDto
     * @param string|null $manufacturer Производитель
     * @param string|null $country Страна производства
     * @param string $created_at Дата создания
     */
    public function __construct(
        int $id,
        string $title,
        string $short_description,
        string $long_description,
        ?string $info,
        ?string $article,
        ?float $price,
        int $in_stock,
        array $images,
        ?string $manufacturer,
        ?string $country,
        string $created_at
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->short_description = $short_description;
        $this->long_description = $long_description;
        $this->info = $info;
        $this->article = $article;
        $this->price = $price;
        $this->in_stock = $in_stock;
        $this->images = $images;
        $this->manufacturer = $manufacturer;
        $this->country = $country;
        $this->created_at = $created_at;
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'info' => $this->info,
            'article' => $this->article,
            'price' => $this->price,
            'in_stock' => $this->in_stock,
            'images' => array_map(fn($img) => $img instanceof ProductImageResponseDto ? $img->toArray() : $img, $this->images),
            'manufacturer' => $this->manufacturer,
            'country' => $this->country,
            'created_at' => $this->created_at,
        ];
    }
}
