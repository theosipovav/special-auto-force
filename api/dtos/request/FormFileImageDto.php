<?php

namespace app\dtos\request;

use yii\base\Model;

/**
 * DTO для загружаемого изображения.
 *
 * @SWG\Definition(
 *     definition="FormFileImageDto",
 *     type="object",
 *     description="Модель для загружаемого изображения",
 *     required={"title", "base64"},
 *
 *     @SWG\Property(property="base64", type="string", description="Содержимое картинки base64"),
 *     @SWG\Property(property="isMain", type="boolean", description="Является главной"),
 * )
 */
class FormFileImageDto extends Model
{
    public ?int $image_id = null;  // ID существующего изображения (для обновления)
    public string $title = '';
    public string $base64 = '';     // Base64 нового изображения
    public bool $isMain = false;

    public function rules()
    {
        return [
            [['image_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['base64'], 'string'],
            [['isMain'], 'boolean'],
            [['image_id', 'base64'], 'validateImageSource'],
        ];
    }

    public function validateImageSource($attribute, $params)
    {
        // Проверяем только один раз — для image_id
        if ($attribute !== 'image_id') {
            return;
        }
        if (empty($this->image_id) && empty($this->base64)) {
            $this->addError('image_id', 'Необходимо указать image_id или base64.');
            $this->addError('base64', 'Необходимо указать image_id или base64.');
        }
    }
}
