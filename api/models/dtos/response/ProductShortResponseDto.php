<?php

namespace app\models\dtos\response;

use app\models\entities\ProductEntity;

/**
 * DTO ответа с данными товара.
 *
 * @SWG\Definition(definition="ProductShortResponseDto", required={"id", "title", "price", "in_stock", "mainImage"},
 *     @SWG\Property(property="id", type="integer", description="Идентификатор товара"),
 *     @SWG\Property(property="title", type="string", description="Название товара"),
 *     @SWG\Property(property="article", type="string", description="Артикул"),
 *     @SWG\Property(property="price", type="number", format="float", description="Цена товара"),
 *     @SWG\Property(property="in_stock", type="integer", enum={0, 1}, description="Наличие на складе: 1 - в наличии, 0 - под заказ"),
 *     @SWG\Property(property="mainImageUrl", type="string", description="Главное/основное изображение продукции, url"),
 *     @SWG\Property(property="categories", type="array", description="Категории (наименования)", @SWG\Items(type="string"))
 * )
 */
class ProductShortResponseDto
{
    public int $id;
    public string $title;
    public string $article;
    public ?float $price;
    public int $in_stock;
    public string $mainImageUrl;
    public array $categories;

    /**
     * @param int $id Идентификатор
     * @param string $title Название
     * @param string $article Артикул
     * @param float|null $price Цена
     * @param int $in_stock Наличие на складе (1 - в наличии, 0 - под заказ)
     * @param string $mainImageUrl Главное (основное) изображение продукции (url)
     * @param array $categories Категории (наименования)
     */
    public function __construct(int $id, string $title, $article, ?float $price, int $in_stock, string $mainImageUrl, $categories) {
        $this->id = $id;
        $this->title = $title;
        $this->article = $article;
        $this->price = $price;
        $this->in_stock = $in_stock;
        $this->mainImageUrl = $mainImageUrl;
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
            'article' => $this->article,
            'price' => $this->price,
            'in_stock' => $this->in_stock,
            'mainImageUrl' => $this->mainImageUrl,
            'categories' => array_map(fn($category) => $category instanceof CategoryResponseDto ? $category->toArray() : $category, $this->categories),
        ];
    }



    /**
     * Фабричный метод для создания DTO из модели ProductEntity и подготовленных данных.
     *
     * @param ProductEntity $product Модель товара
     * @param array $productImageDtos Все связанные изображения (ProductImageResponseDto)
     * @param array $categories Категории
     * @return ProductShortResponseDto
     */
    public static function createFromProduct(ProductEntity $product, array $productImageDtos, array $categories): self
    {
        $mainImageUrl = null;
        foreach ($productImageDtos as $key => $productImageDto) {
            if ($mainImageUrl === null && $productImageDto->is_main) {
                $mainImageUrl = $productImageDto->url;
            } 
        }
        $categoryTitles = array_map(fn($c)=> $c->title, $categories);
        return new ProductShortResponseDto(
            (int) $product->id,
            (string) $product->title,
            (string) $product->article,
            $product->price !== null ? (float) $product->price : null,
            (int) $product->in_stock,
            $mainImageUrl ?? '',
            $categoryTitles
        );
    }
}
