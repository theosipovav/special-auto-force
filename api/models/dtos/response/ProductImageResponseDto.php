<?php

namespace app\models\dtos\response;


use app\models\entities\ProductImageEntity;

/**
 * DTO ответа с данными изображения товара.
 *
 * @SWG\Definition(
 *     definition="ProductImageResponseDto",
 *     required={"product_id", "image_id", "is_main", "title", "url"},
 *
 *     @SWG\Property(property="product_id", type="integer", description="Идентификатор товара"),
 *     @SWG\Property(property="image_id", type="integer", description="Идентификатор изображения"),
 *     @SWG\Property(property="is_main", type="boolean", description="Является ли изображение главным"),
 *     @SWG\Property(property="title", type="string", description="Название изображения"),
 *     @SWG\Property(property="url", type="string", format="uri", description="URL изображения")
 * )
 */
class ProductImageResponseDto
{
    public int $product_id;
    public int $image_id;
    public bool $is_main;
    public string $title;
    public string $url;

    public function __construct(int $product_id, int $image_id, bool $is_main, string $title, string $url)
    {
        $this->product_id = $product_id;
        $this->image_id = $image_id;
        $this->is_main = $is_main;
        $this->title = $title;
        $this->url = $url;
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'image_id' => $this->image_id,
            'is_main' => $this->is_main,
            'title' => $this->title,
            'url' => $this->url,
        ];
    }


    
    /**
     * Фабричный метод для создания DTO из модели ProductImageEntity.
     * 
     * Модель ProductImageEntity должна быть загружена с关联шей imageEntity 
     * (например, через with(['productImages.imageEntity'])).
     *
     * @param ProductImageEntity $productImage Модель связи товара с изображением
     * @return self
     */
    public static function create(ProductImageEntity $productImage): self
    {
        $url = '';
        if ($productImage->imageEntity !== null) {
            $url = $productImage->imageEntity->url ?? '';
        }

        return new self(
            (int) $productImage->product_id,
            (int) $productImage->image_id,
            (bool) $productImage->is_main,
            (string) $productImage->title,
            (string) $url
        );
    }

}
