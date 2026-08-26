<?php

namespace app\models\dtos\request;

use yii\base\Model;

/**
 * DTO для обновления категории.
 *
 * @SWG\Definition(definition="CategoryRequestDto",
 *     @SWG\Property(property="title", type="string", maxLength=255, description="Название категории"),
 *     @SWG\Property(property="description", type="string", description="Описание категории"),
 *     @SWG\Property(property="base64", type="string", description="Изображение в формате Base64"),
 *     @SWG\Property(property="updateImage", type="boolean", description="Обновить или удалить текущее изображение (при NULLL в base64 будет просто удалено текущение избражение)")
 * )
 */
class CategoryRequestDto extends Model
{
    /**
     * Название категории
     */
    public string $title;

    /**
     * Описание категории
     */
    public ?string $description = null;

    /**
     * Изображение в формате Base64
     */
    public ?string $base64 = null;

    /**
     * Обновить текущее изображение
     */
    public bool $updateImage = false;

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['base64'], 'string'],
            [['updateImage'], 'boolean'],
        ];
    }
}