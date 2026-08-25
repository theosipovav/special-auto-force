<?php

namespace app\models\dto\response;

/**
 * DTO для ответа с данными изображения товара
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
}
