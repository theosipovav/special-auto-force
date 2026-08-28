<?php

namespace app\dtos\response;

use Yii;
use app\entities\CategoryEntity;
use app\entities\ImageEntity;

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
 *     @SWG\Property(property="imageId", type="integer", description="Идентификатор изображения, url"),
 *     @SWG\Property(property="productsCount", type="integer", description="Количество продукции в категории, url"),
 * )
 */
class CategoryResponseDto
{
    public int $id;
    public string $title;
    public ?string $description;
    public ?string $imageUrl;
    public ?int $imageId;
    public int $productsCount = 10;

    public function __construct(int $id, string $title, ?string $description = null, ?string $imageUrl, ?int $imageId, ?int $productsCount)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->imageUrl = $imageUrl ?? rtrim(Yii::getAlias('@web'), '/') . '/web/images/no_image.jpg';
        $this->imageId = $imageId;
        $this->productsCount = $productsCount ?? 0;
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
            'productsCount' => $this->productsCount,
        ];
    }




    /**
     * Фабричный метод для создания DTO из модели CategoryEntity и ImageEntity.
     *
     * @param CategoryEntity $category Категория
     * @param ImageEntity $image Изображение
     * @param int $productsCount Количество продукции в категории
     * @return CategoryResponseDto
     */
    public static function create(CategoryEntity $category, ?ImageEntity $image, ?int $productsCount): self
    {
        return new CategoryResponseDto(
            (int) $category->id,
            (string) $category->title,
            $category->description,
            $image->url ?? null,
            $image->id ?? null,
            $productsCount,
        );
    }
}
