<?php

namespace app\models\dtos\response;

use app\models\entities\CategoryEntity;
use app\models\entities\ImageEntity;

/**
 * DTO ответа с данными категории.
 *
 * @SWG\Definition(
 *     definition="CategoryResponseDto",
 *     required={"id", "title"},
 *
 *     @SWG\Property(property="id", type="integer", description="Идентификатор категории"),
 *     @SWG\Property(property="title", type="string", description="Название категории"),
 *     @SWG\Property(property="description", type="string", description="Описание категории"),
 *     @SWG\Property(property="imageUrl", type="string", description="Сылка на изображение категории, url"),
 *     @SWG\Property(property="imageId", type="integer", description="Идентификатор изображегния, url"),
 * )
 */
class CategoryResponseDto
{
    public int $id;
    public string $title;
    public ?string $description;
    public ?string $imageUrl;
    public ?int $imageId;

    public function __construct(int $id, string $title, ?string $description = null, ?string $imageUrl = null, ?int $imageId = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->imageId = $imageId;
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
            'imageUrl' => $this->imageUrl,
            'imageId' => $this->imageId,
        ];
    }




    /**
     * Фабричный метод для создания DTO из модели CategoryEntity и ImageEntity.
     *
     * @param CategoryEntity $category Категория
     * @param ImageEntity $image Изображение
     * @return CategoryResponseDto
     */
    public static function create(CategoryEntity $category, ImageEntity $image): self
    {
        return new CategoryResponseDto(
            (int) $category->id,
            (string) $category->title,
            $category->description,
            $image->url ?? null,
            $image->id ?? null
        );
    }
}
