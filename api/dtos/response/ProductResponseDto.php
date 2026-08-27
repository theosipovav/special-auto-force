<?php

namespace app\dtos\response;

use app\entities\ProductEntity;

/**
 * DTO ответа с данными товара.
 *
 * @SWG\Definition(definition="ProductResponseDto", required={"id", "title", "short_description", "long_description", "price", "in_stock", "created_at", "mainImage", "images"},
 *     @SWG\Property(property="id", type="integer", description="Идентификатор товара"),
 *     @SWG\Property(property="title", type="string", description="Название товара"),
 *     @SWG\Property(property="short_description", type="string", description="Краткое описание товара"),
 *     @SWG\Property(property="long_description", type="string", description="Подробное описание товара"),
 *     @SWG\Property(property="info", type="string", description="Дополнительная информация"),
 *     @SWG\Property(property="article", type="string", description="Артикул / заводской код"),
 *     @SWG\Property(property="price", type="number", format="float", description="Цена товара"),
 *     @SWG\Property(property="in_stock", type="integer", enum={0, 1}, description="Наличие на складе: 1 - в наличии, 0 - под заказ"),
 *     @SWG\Property(property="manufacturer", type="string", description="Производитель"),
 *     @SWG\Property(property="country", type="string", description="Страна производства"),
 *     @SWG\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @SWG\Property(property="mainImageUrl", type="string", description="Главное/основное изображение продукции, url"),
 *     @SWG\Property(property="otherImageUrls", type="array", description="Коллекция изображений товара, коллекция url", @SWG\Items(type="string")),
 *     @SWG\Property(property="images", ref="#/definitions/ProductImageResponseDto", description="Все связанные изображения"),
 *     @SWG\Property(property="categories", type="array", description="Коллекция категорий товара", @SWG\Items(ref="#/definitions/CategoryResponseDto"))
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
    public ?string $manufacturer;
    public ?string $country;
    public string $created_at;
    public ?string $mainImageUrl;
    public array $otherImageUrls;
    public array $images;
    public array $categories;

    /**
     * @param int $id Идентификатор
     * @param string $title Название
     * @param string $short_description Краткое описание
     * @param string $long_description Подробное описание
     * @param string|null $info Дополнительная информация
     * @param string|null $article Артикул / Заводской код
     * @param float|null $price Цена
     * @param int $in_stock Наличие на складе (1 - в наличии, 0 - под заказ)
     * @param string|null $manufacturer Производитель
     * @param string|null $country Страна производства
     * @param string $created_at Дата создания
     * @param string $mainImageUrl Главное (основное) изображение продукции (url)
     * @param array $otherImageUrls Коллекция изображений товара, коллекция url
     * @param array $images Все связанные изображения
     * @param array $categories Коллекция категорий товара
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
        ?string $manufacturer,
        ?string $country,
        string $created_at,
        ?string $mainImageUrl,
        array $otherImageUrls,
        array $images,
        array $categories
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->short_description = $short_description;
        $this->long_description = $long_description;
        $this->info = $info;
        $this->article = $article;
        $this->price = $price;
        $this->in_stock = $in_stock;
        $this->manufacturer = $manufacturer;
        $this->country = $country;
        $this->created_at = $created_at;
        $this->mainImageUrl = $mainImageUrl;
        $this->otherImageUrls = $otherImageUrls;
        $this->images = $images;
        $this->categories = $categories;
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
            'manufacturer' => $this->manufacturer,
            'country' => $this->country,
            'created_at' => $this->created_at,
            'mainImageUrl' => $this->mainImageUrl,
            'otherImageUrls' => $this->otherImageUrls,
            'images' => $this->images,
            'categories' => array_map(fn($category) => $category instanceof CategoryResponseDto ? $category->toArray() : $category, $this->categories),
        ];
    }



    /**
     * Фабричный метод для создания DTO из модели ProductEntity и подготовленных данных.
     *
     * @param ProductEntity $product Модель товара
     * @param array $productImageDtos Все связанные изображения (ProductImageResponseDto)
     * @return ProductResponseDto
     */
    public static function createFromProduct(ProductEntity $product, array $productImageDtos, array $categoryDtos): self
    {
        $mainImageUrl = null;
        $otherImageUrls = [];
        foreach ($productImageDtos as $key => $productImageDto) {
            if ($mainImageUrl === null && $productImageDto->is_main) {
                $mainImageUrl = $productImageDto->url;
            } else {
                $otherImageUrls[] = $productImageDto->url;
            }
        }


        return new ProductResponseDto(
            (int) $product->id,
            (string) $product->title,
            (string) $product->short_description,
            (string) $product->long_description,
            $product->info,
            $product->article,
            $product->price !== null ? (float) $product->price : null,
            (int) $product->in_stock,
            $product->manufacturer,
            $product->country,
            (string) $product->created_at,
            $mainImageUrl,
            $otherImageUrls,
            $productImageDtos,
            $categoryDtos
        );
    }
}
