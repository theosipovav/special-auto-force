<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\models\entities\CategoryEntity;
use app\models\entities\ImageEntity;
use app\models\dtos\request\CategoryRequestDto;
use app\models\dtos\response\CategoryResponseDto;

/**
 * Категории (администратор)
 *
 * @SWG\Tag(
 *     name="admin / category controller",
 *     description="Управление категориями товаров."
 * )
 */
class CategoryController extends BaseApiAdminController
{
    public $modelClass = CategoryEntity::class;

    public function actions()
    {
        $actions = parent::actions();

        // Удаляем все, кроме разрешённых views
        $allowed = [];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }
        return $actions;
    }


    /**
     * Создание новой категории.
     *
     * @SWG\Post(
     *     path="/admin/category",
     *     tags={"admin / category controller"},
     *     operationId="adminCategoryCreate",
     *     summary="Создать категорию",
     *     description="Создает новую категорию и опционально привязывает к ней изображение (Base64).",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="body", in="body", description="Данные новой категории.", required=true, @SWG\Schema(ref="#/definitions/CategoryRequestDto")),
     *     @SWG\Response(response=201, description="Категория успешно создана", @SWG\Schema(ref="#/definitions/CategoryResponseDto")),
     *     @SWG\Response(response=422, description="Ошибка валидации данных"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка при создании категории")
     * )
     */
    public function actionCreate()
    {
        $this->checkAccess();

        $request = new CategoryRequestDto();
        $request->load(Yii::$app->request->getBodyParams(), '');

        if (!$request->validate()) {
            $errorString = json_encode($request->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации CreateCategoryRequest: ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Проверьте правильность заполнения данных и повторите запрос');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $category = new CategoryEntity();
            $category->title = $request->title;
            $category->description = $request->description;

            // 2. Обрабатываем изображение, если передан base64
            if (!empty($request->base64)) {
                $image = new ImageEntity();
                if (!$image->saveImageFromBase64($request->base64)) {
                    throw new \Exception('Не удалось сохранить изображение категории из base64');
                }
                if (!$image->save()) {
                    throw new \Exception('Не удалось сохранить изображение: ' . json_encode($image->getErrors(), JSON_UNESCAPED_UNICODE));
                }
                $category->image_id = $image->id;
            }

            if (!$category->save()) {
                throw new \Exception('Не удалось сохранить категорию: ' . json_encode($category->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }

            $transaction->commit();

            Yii::$app->response->statusCode = 201;
            return $this->buildCategoryResponseDto($category);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Не удалось создать категорию: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось создать категорию');
        }
    }

    /**
     * Обновление категории.
     *
     * Логика работы с изображением:
     * - Если передан `base64` — старое изображение удаляется, создаётся новое.
     * - Если `updateImage = true` — будет обновлено текущее изображение (при NULLL в base64 будет просто удалено текущение избражение)
     * - Если ничего не передано — изображение остаётся без изменений.
     *
     * @SWG\Put(
     *     path="/admin/category/{id}",
     *     tags={"admin / category controller"},
     *     operationId="adminCategoryUpdate",
     *     summary="Обновить категорию",
     *     description="Обновляет поля категории и опционально её изображение.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", description="ID категории", required=true, type="integer", format="int32"),
     *     @SWG\Parameter(name="body", in="body", description="Данные для обновления.", required=true, @SWG\Schema(ref="#/definitions/CategoryRequestDto")),
     *     @SWG\Response(response=200, description="Категория успешно обновлена", @SWG\Schema(ref="#/definitions/CategoryResponseDto")),
     *     @SWG\Response(response=404, description="Категория не найдена"),
     *     @SWG\Response(response=422, description="Ошибка валидации данных"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка при обновлении категории")
     * )
     */
    public function actionUpdate(int $id)
    {
        $this->checkAccess();

        $category = CategoryEntity::findOne($id);
        if (!$category) {
            throw new NotFoundHttpException("Категория #{$id} не найдена.");
        }

        $request = new CategoryRequestDto();
        $request->load(Yii::$app->request->getBodyParams(), '');

        if (!$request->validate()) {
            $errorString = json_encode($request->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации UpdateCategoryRequest: ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Проверьте правильность заполнения данных и повторите запрос');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $category->title = $request->title;
            $category->description = $request->description;
            
            if ($request->updateImage) {
                $removeImage = ImageEntity::findOne($category->image_id);
                $removeImage->DeleteLocalFile();
                $category->image_id = null;
                if (!$removeImage->delete()) {
                    throw new \Exception('Не удалось удалить старое изображение: ' . json_encode($removeImage->getErrors(), JSON_UNESCAPED_UNICODE));
                }
                if (!empty($request->base64)) {
                    $image = new ImageEntity();
                    if (!$image->saveImageFromBase64($request->base64)) {
                        throw new \Exception('Не удалось сохранить изображение категории из base64');
                    }
                    if (!$image->save()) {
                        throw new \Exception('Не удалось сохранить изображение: ' . json_encode($image->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                    $category->image_id = $image->id;
                }
            }
            if (!$category->save()) {
                throw new \Exception('Не удалось сохранить категорию: ' . json_encode($category->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
            $transaction->commit();
            Yii::$app->response->statusCode = 201;
            return $this->buildCategoryResponseDto($category);
        } catch (UnprocessableEntityHttpException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Не удалось обновить категорию #{$id}: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось обновить категорию');
        }
    }


    /**
     * Удаление категории.
     *
     * Вместе с категорией удаляется связанный файл изображения (если есть).
     * Связи с товарами (ProductCategoryEntity) удаляются каскадно.
     *
     * @SWG\Delete(
     *     path="/admin/category/{id}",
     *     tags={"admin / category controller"},
     *     operationId="adminCategoryDelete",
     *     summary="Удалить категорию",
     *     description="Удаляет категорию, её связи с товарами и связанный файл изображения.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="id", in="path", description="ID категории", required=true, type="integer", format="int32"),
     *     @SWG\Response(response=204, description="Категория успешно удалена"),
     *     @SWG\Response(response=404, description="Категория не найдена"),
     *     @SWG\Response(response=401, description="Пользователь не авторизован"),
     *     @SWG\Response(response=403, description="Недостаточно прав"),
     *     @SWG\Response(response=500, description="Ошибка при удалении категории")
     * )
     */
    public function actionDelete(int $id)
    {
        $this->checkAccess();

        $category = CategoryEntity::findOne($id);
        if (!$category) {
            throw new NotFoundHttpException("Категория #{$id} не найдена.");
        }


        $transaction = Yii::$app->db->beginTransaction();
        try {

            if (!empty($category->image_id)) {
                $removeImage = ImageEntity::findOne($category->image_id);
                $removeImage->DeleteLocalFile();
                if (!$removeImage->delete()) {
                    throw new \Exception('Не удалось удалить изображения: ' . json_encode($removeImage->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                }
            }
            if (!$category->delete()) {
                throw new \Exception('Не удалось удалить файл изображения: ' . json_encode($category->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }



            $transaction->commit();
            Yii::$app->response->statusCode = 204;
            return null;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Ошибка при удалении категории #{$id}: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Ошибка при удалении категории и связанных данных.');
        }
    }


    /**
     * Формирует CategoryResponseDto из модели CategoryEntity.
     *
     * @param CategoryEntity $category
     * @return CategoryResponseDto
     */
    private function buildCategoryResponseDto(CategoryEntity $category): CategoryResponseDto
    {
        $category->refresh();

        $image = $category->imageEntity;
        return CategoryResponseDto::create($category, $image);
    }
}
