<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\entities\ImageEntity;
use yii\web\ServerErrorHttpException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Файлы изображения (администратор)
 *
 * @SWG\Tag(
 *     name="admin / image controller",
 *     description="Управление изображениями."
 * )
 */
class ImageController extends BaseApiAdminController
{
    public $modelClass = ImageEntity::class;

    /**
     * @SWG\Get(
     *   tags={"admin / image controller"},
     *   path="/admin/images",
     *   summary="Список изображений с фильтрацией по названию",
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(name="title", in="query", type="string", description="Фильтр по названию (LIKE %title%)"),
     *   @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы (по умолчанию 1)"),
     *   @SWG\Parameter(name="per-page", in="query", type="integer", description="Количество записей на странице (по умолчанию 50)"),
     * 
     *   @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/ImageEntity")),
     *       @SWG\Property(property="_links", type="object"),
     *       @SWG\Property(property="_meta", type="object")
     *     )
     *   ),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён (недостаточно прав)")
     * )
     */
    public function actions()
    {
        $actions = parent::actions();

        // Список допущенных действий из стандартых CRUD
        $allowed = ['index'];
        // Удаляем все, кроме разрешённых
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }

        $actions['index']['prepareDataProvider'] = function () {
            $query = ImageEntity::find();
            $title = Yii::$app->request->get('title');
            if (!empty($title)) {
                $query->andWhere(['like', 'title', $title]);
            }
            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        };
        return $actions;
    }


    /**
     * POST /admin/image
     * Создание нового изображения (загрузка файла)
     * 
     * @SWG\Post(
     *   tags={"admin / image controller"},
     *   path="/admin/image",
     *   summary="Загрузка нового изображения",
     *   security={{"Bearer": {}}},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(in="formData", name="file", type="file", required=true, description="Загружаемый файл изображения"),

     *   @SWG\Response(response=201, description="Запись успешно создана", @SWG\Schema(ref="#/definitions/ImageEntity")),
     *   @SWG\Response(response=422, description="Ошибка валидации данных или загрузки файла"),
     *   @SWG\Response(response=401, description="Не авторизован" ),  
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     */
    public function actionCreate()
    {
        $this->checkAccess();
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('Файл не передан.');
        }
        $image = new ImageEntity();
        try {
            $image->UploadedFilBuFormFilee($uploadedFile);
        } catch (\Exception $e) {
            Yii::error("Не удалось создать запись: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось создать запись');
        }
        if ($image->save()) {
            Yii::$app->response->statusCode = 201;
            return $image->toArray();
        }
        Yii::error('Не удалось создать запись: ' . json_encode($image->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), __METHOD__);
        throw new ServerErrorHttpException('Не удалось создать запись');
    }

    /**
     * @SWG\Delete(
     *   tags={"admin / image controller"},
     *   path="/admin/image/{id}",
     *   summary="Удаление изображения",
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID изображения"),
     *   @SWG\Response(response=204, description="Успешно удалено (нет содержимого)"),
     *   @SWG\Response(response=404, description="Изображение не найдено"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     */
    public function actionDelete(int $id)
    {
        $this->checkAccess();
        $image = ImageEntity::findOne($id);
        if (!$image) {
            throw new NotFoundHttpException("Изображение не найдено.");
        }
        try {
            $image->DeleteLocalFile();
        } catch (\Exception $e) {
            Yii::error("Не удалось удалить запись: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось удалить запись');
        }

        if (!$image->delete()) {
            Yii::error("Не удалось удалить запись: " . json_encode($image->getErrors()), __METHOD__);
            throw new ServerErrorHttpException('Не удалось удалить запись');
        }
        return null;
    }
}
