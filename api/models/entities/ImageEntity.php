<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Модель сущности - изображение
 *
 * @SWG\Definition(
 *     definition="ImageEntity",
 *     type="object",
 *
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         description="Идентификатор"
 *     ),
 * 
 *     @SWG\Property(
 *         property="title",
 *         type="string",
 *         description="Название картинки"
 *     ),
 * 
 *     @SWG\Property(
 *         property="path",
 *         type="string",
 *         description="Относительный путь в файловой системе"
 *     ),
 *
 *     @SWG\Property(
 *         property="url",
 *         type="string",
 *         description="URL для открытия"
 *     ),
 * )
 *
 * @property int $id Идентификатор
 * @property string $title Название картинки
 * @property string $path Относительный путь в файловой системе
 * @property string $url URL для открытия
 */
class ImageEntity extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%image}}';
    }

    public function rules()
    {
        return [
            [['title', 'path', 'url'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['title', 'path', 'url'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'title' => 'Название картинки',
            'path' => 'Относительный путь в файловой системе',
            'url' => 'URL для открытия',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'path',
            'url',
        ];
    }

    public function extraFields()
    {
        return [];
    }



    /**
     * Загрузить файл в каталог сервера
     * 
     * @return bool true при успешном удалении
     * @throws \Exception При возникновениее ошибки
     */
    public function UploadedFilBuFormFilee(UploadedFile $uploadedFile): bool
    {
        if ($uploadedFile->hasError) {
            throw new \Exception('Ошибка загрузки файла: ' . $uploadedFile->error);
        }
        // Генерируем уникальное имя файла
        $extension = $uploadedFile->getExtension();
        $fileName = Yii::$app->security->generateRandomString(32) . '.' . $extension;

        // Путь для сохранения (относительно @webroot)
        $fullPath = Yii::getAlias('@webroot/uploads/images/') . $fileName;

        // Сохраняем файл
        if (!$uploadedFile->saveAs($fullPath)) {
            throw new \Exception('Не удалось сохранить файл.');
        }
        $this->title = Yii::$app->request->getBodyParam('title', '');
        $this->path = $fullPath;
        $this->url = rtrim(Yii::getAlias('@web'), '/') . '/uploads/images/' . $fileName;
        // $this->url = Url::to('@web/uploads/images/' . $fileName, true); // полный абсолютный URL
        return true;
    }



    /**
     * Удаляет файл по относительному или абсолютному пути.
     *
     * @return bool true при успешном удалении
     * @throws \Exception При возникновениее ошибки
     */
    public function DeleteLocalFile(): bool
    {
        $fullPath = $this->path;
        // Проверка существования файла
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            Yii::info("Файл не найден для удаления: {$fullPath}", __METHOD__);
            throw new \Exception('Файл не найден.');
        }
        // Пытаемся удалить
        if (!@unlink($fullPath)) {
            Yii::error("Не удалось удалить файл: {$fullPath}", __METHOD__);
            throw new \Exception('Не удалось удалить файл.');
        }
        return true;
    }
}
