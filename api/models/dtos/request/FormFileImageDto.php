<?php

namespace app\models\dtos\request;

use yii\base\Model;

/**
 * DTO для загружаемого изображения.
 *
 * @SWG\Definition(
 *     definition="FormFileImageDto",
 *     type="object",
 *     description="Модель для загружаемого изображения",
 *     required={"title", "image"},
 *
 *     @SWG\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         description="Наименование"
 *     ),
 *     @SWG\Property(
 *         property="image",
 *         type="string",
 *         description="Содержимое картинки base64"
 *     ),
 * 
 *     @SWG\Property(
 *         property="isMain",
 *         type="boolean",
 *         description="Является главной"
 *     ),
 * )
 */
class FormFileImageDto extends Model
{
    /**
     * Наименование
     */
    public string $title;

    /**
     * Содержимое картинки base64
     */
    public string $image;

    /**
     * Является главной
     */
    public bool $isMain = false;
}
