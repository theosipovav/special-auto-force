<?php

namespace app\models\dtos\response;

/**
 * DTO ответа с данными категории.
 *
 * @SWG\Definition(
 *     definition="CategoryResponseDto",
 *     required={"id", "title"},
 *
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         description="Идентификатор категории"
 *     ),
 *     @SWG\Property(
 *         property="title",
 *         type="string",
 *         description="Название категории"
 *     ),
 *     @SWG\Property(
 *         property="description",
 *         type="string",
 *         description="Описание категории"
 *     ),
 *     @SWG\Property(
 *         property="image",
 *         type="string",
 *         description="Сылка на изображение категории"
 *     )
 * )
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
