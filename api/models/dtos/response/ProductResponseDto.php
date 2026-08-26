<?php

namespace app\models\dtos\response;

/**
 * DTO ответа с данными товара.
 *
 * @SWG\Definition(
 *     definition="ProductResponseDto",
 *     required={
 *         "id",
 *         "title",
 *         "short_description",
 *         "long_description",
 *         "price",
 *         "in_stock",
 *         "images",
 *         "created_at"
 *     },
 *
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         description="Идентификатор товара"
 *     ),
 *     @SWG\Property(
 *         property="title",
 *         type="string",
 *         description="Название товара"
 *     ),
 *     @SWG\Property(
 *         property="short_description",
 *         type="string",
 *         description="Краткое описание товара"
 *     ),
 *     @SWG\Property(
 *         property="long_description",
 *         type="string",
 *         description="Подробное описание товара"
 *     ),
 *     @SWG\Property(
 *         property="info",
 *         type="string",
 *         description="Дополнительная информация"
 *     ),
 *     @SWG\Property(
 *         property="article",
 *         type="string",
 *         description="Артикул / заводской код"
 *     ),
 *     @SWG\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Цена товара"
 *     ),
 *     @SWG\Property(
 *         property="in_stock",
 *         type="integer",
 *         enum={0, 1},
 *         description="Наличие на складе: 1 - в наличии, 0 - под заказ"
 *     ),
 *     @SWG\Property(
 *         property="images",
 *         type="array",
 *         description="Изображения товара",
 *         @SWG\Items(
 *             ref="#/definitions/ProductImageResponseDto"
 *         )
 *     ),
 *     @SWG\Property(
 *         property="manufacturer",
 *         type="string",
 *         description="Производитель"
 *     ),
 *     @SWG\Property(
 *         property="country",
 *         type="string",
 *         description="Страна производства"
 *     ),
 *     @SWG\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата создания"
 *     )
 * )
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
