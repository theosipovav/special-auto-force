<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\entities\ImageEntity;

/**
 * Файлы изображения (администратор)
 *
 * @SWG\Tag(
 *     name="Файлы изображения (admin)",
 *     description="Управление изображениями (CRUD). Доступно только администраторам и менеджерам."
 * )
 */
class ImageController extends BaseApiAdminController
{
    public $modelClass = ImageEntity::class;


    public function actions()
    {
        $actions = parent::actions();

        // Список разрешённых действий
        $allowed = ['index', 'create', 'delete'];
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
     *   path="/admin/image",
     *   summary="Загрузка нового изображения",
     *   security={{"Bearer": {}}},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(in="formData", name="file", type="file", required=true, description="Загружаемый файл изображения"),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="title",
     *     type="string",
     *     required=false,
     *     description="Название изображения (если не указано, используется имя файла)"
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Запись успешно создана",
     *     @SWG\Schema(ref="#/definitions/ImageEntity")
     *   ),
     *   @SWG\Response(
     *     response=422,
     *     description="Ошибка валидации данных или загрузки файла"
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="Не авторизован"
     *   ),
     *   @SWG\Response(
     *     response=403,
     *     description="Доступ запрещён"
     *   )
     * )
     */
    public function actionCreate()
    {
        $this->checkAccess('create');
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('file');
        if (!$uploadedFile) {
            throw new \yii\web\BadRequestHttpException('Файл не передан.');
        }
        $image = new ImageEntity();
        try {
            $image->UploadedFilBuFormFilee($uploadedFile);
        } catch (\Exception $e) {
            Yii::error("Не удалось создать запись: " . $e->getMessage(), __METHOD__);
            throw new yii\web\ServerErrorHttpException('Не удалось создать запись');
        }
        if ($image->save()) {
            Yii::$app->response->statusCode = 204;
            return $image->toArray();
        }
        throw new \yii\web\UnprocessableEntityHttpException('Ошибка валидации данных');
    }

    /**
     * DELETE /api/images/{id}
     * Удаление изображения
     */
    public function actionDelete(int $id)
    {
        $image = ImageEntity::findOne($id);
        if (!$image) {
            throw new \yii\web\NotFoundHttpException("Изображение не найдено.");
        }
         try {
            $image->DeleteLocalFile();
        } catch (\Exception $e) {
            Yii::error("Не удалось удалить запись: " . $e->getMessage(), __METHOD__);
            throw new yii\web\ServerErrorHttpException('Не удалось удалить запись');
        }

        if (!$image->delete()) {
            Yii::error("Не удалось удалить запись: " . json_encode($image->getErrors()), __METHOD__);
            throw new \yii\web\ServerErrorHttpException('Не удалось удалить запись');
        }
        return null;
    }
}
