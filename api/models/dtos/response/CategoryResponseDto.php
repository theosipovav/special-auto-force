<?php

namespace app\models\dto\response;

/**
 * DTO для ответа с данными категории
 */
class CategoryResponseDto
{
    public int $id;
    public string $title;
    public ?string $description;
    public ?string $image;

    public function __construct(int $id, string $title, ?string $description = null, ?string $image = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
        ];
    }
}
