<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\models\entities\CustomerRequestEntity;
use app\models\entities\Product;
use app\models\entities\Parameter;

/**
 * Заявки клиентов (администратор)
 *
 * @SWG\Tag(
 *     name="admin / request controller",
 *     description="Управление заявками клиентов."
 * )
 */
class RequestController extends BaseApiAdminController
{
    public $modelClass = CustomerRequestEntity::class;


    /**
     * 
     * @SWG\Get(
     *   tags={"admin / request controller"},
     *   path="/admin/requests",
     *   summary="Список заявок клиентов",
     *   description="Возвращает список заявок с пагинацией и фильтрацией по статусу, телефону, email и общему поиску.",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="status", in="query", type="string", enum={"new", "processing", "completed", "cancelled"}, description="Фильтр по статусу"),
     *   @SWG\Parameter(name="phone", in="query", type="string", description="Фильтр по телефону (LIKE)"),
     *   @SWG\Parameter(name="email", in="query", type="string", description="Фильтр по email (LIKE)"),
     *   @SWG\Parameter(name="q", in="query", type="string", description="Общий поиск по телефону, email, пожеланиям и заметкам"),
     *   @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы (по умолчанию 1)"),
     *   @SWG\Parameter(name="per-page", in="query", type="integer", description="Записей на странице (по умолчанию 20)"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ",
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/CustomerRequestEntity")),
     *           @SWG\Property(property="_links", type="object"),
     *           @SWG\Property(property="_meta", type="object")
     *       )
     *   ),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     * 
     * @SWG\Get(
     *   tags={"admin / request controller"},
     *   path="/admin/request/{id}",
     *   summary="Получить заявку по ID",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID заявки"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(ref="#/definitions/CustomerRequestEntity")),
     *   @SWG\Response(response=404, description="Заявка не найдена"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     * 
     * 
     * @SWG\Put(
     *   tags={"admin / request controller"},
     *   path="/admin/request/{id}",
     *   summary="Обновить заявку",
     *   description="Обновляет поля заявки (phone, email, wishlist, admin_notes, status). Поле updated_at обновляется автоматически.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID заявки"),
     *   @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="phone", type="string", maxLength=32),
     *           @SWG\Property(property="email", type="string", format="email"),
     *           @SWG\Property(property="wishlist", type="string"),
     *           @SWG\Property(property="admin_notes", type="string"),
     *           @SWG\Property(property="status", type="string", enum={"new", "processing", "completed", "cancelled"})
     *       )
     *   ),
     *
     *   @SWG\Response(response=200, description="Заявка обновлена", @SWG\Schema(ref="#/definitions/CustomerRequestEntity")),
     *   @SWG\Response(response=404, description="Заявка не найдена"),
     *   @SWG\Response(response=422, description="Ошибка валидации"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     * 
     * 
     * @SWG\Delete(
     *   tags={"admin / request controller"},
     *   path="/admin/request/{id}",
     *   summary="Удалить заявку",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID заявки"),
     *
     *   @SWG\Response(response=204, description="Заявка успешно удалена"),
     *   @SWG\Response(response=404, description="Заявка не найдена"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     * 
     */
    public function actions()
    {
        $actions = parent::actions();

        // Разрешённые стандартные действия
        $allowed = ['index', 'view', 'update', 'delete'];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }

        // Кастомный DataProvider для index
        $actions['index']['prepareDataProvider'] = function () {
            $query = CustomerRequestEntity::find()->with('product');

            $status = Yii::$app->request->get('status');
            if (!empty($status)) {
                $query->andWhere(['status' => $status]);
            }

            $phone = Yii::$app->request->get('phone');
            if (!empty($phone)) {
                $query->andWhere(['like', 'phone', $phone]);
            }

            $email = Yii::$app->request->get('email');
            if (!empty($email)) {
                $query->andWhere(['like', 'email', $email]);
            }

            $q = Yii::$app->request->get('q');
            if (!empty($q)) {
                $query->andFilterWhere([
                    'or',
                    ['like', 'phone', $q],
                    ['like', 'email', $q],
                    ['like', 'wishlist', $q],
                    ['like', 'admin_notes', $q],
                ]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                    'pageSizeParam' => 'per-page',
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        };

        return $actions;
    }


    /**
     * Принять заявку в работу.
     * 
     * 
     * @SWG\Post(
     *   tags={"admin / request controller"},
     *   path="/admin/request/{id}/set-processing",
     *   summary="Принять заявку в работу",
     *   description="Переводит заявку в статус 'processing', обновляет updated_at и отправляет клиенту письмо о принятии заказа.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID заявки"),
     *   @SWG\Parameter(name="body", in="body", required=false,
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="admin_notes", type="string", description="Дополнительная заметка менеджера (добавится к заявке)")
     *       )
     *   ),
     *
     *   @SWG\Response(response=200, description="Статус изменён, письмо отправлено", @SWG\Schema(ref="#/definitions/CustomerRequestEntity")),
     *   @SWG\Response(response=404, description="Заявка не найдена"),
     *   @SWG\Response(response=422, description="Заявка уже в этом статусе"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionSetProcessing(int $id)
    {
        return $this->changeStatusAndNotify($id, CustomerRequestEntity::STATUS_PROCESSING, 'processing');
    }


    /**
     * Завершить заявку.
     *
     * 
     * @SWG\Post(
     *   tags={"admin / request controller"},
     *   path="/admin/request/{id}/set-completed",
     *   summary="Завершить заявку",
     *   description="Переводит заявку в статус 'completed', обновляет updated_at и отправляет клиенту письмо о готовности заказа.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID заявки"),
     *   @SWG\Parameter(name="body", in="body", required=false,
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="admin_notes", type="string", description="Дополнительная заметка менеджера")
     *       )
     *   ),
     *
     *   @SWG\Response(response=200, description="Статус изменён, письмо отправлено", @SWG\Schema(ref="#/definitions/CustomerRequestEntity")),
     *   @SWG\Response(response=404, description="Заявка не найдена"),
     *   @SWG\Response(response=422, description="Заявка уже в этом статусе"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionSetCompleted(int $id)
    {
        return $this->changeStatusAndNotify($id, CustomerRequestEntity::STATUS_COMPLETED, 'completed');
    }


    /**
     * Отменить заявку.
     *
     * @SWG\Post(
     *   tags={"admin / request controller"},
     *   path="/admin/request/{id}/set-cancelled",
     *   summary="Отменить заявку",
     *   description="Переводит заявку в статус 'cancelled', обновляет updated_at и отправляет клиенту письмо об отмене заказа.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID заявки"),
     *   @SWG\Parameter(name="body", in="body", required=false,
     *       @SWG\Schema(
     *           type="object",
     *           @SWG\Property(property="admin_notes", type="string", description="Причина отмены / заметка менеджера")
     *       )
     *   ),
     *
     *   @SWG\Response(response=200, description="Статус изменён, письмо отправлено", @SWG\Schema(ref="#/definitions/CustomerRequestEntity")),
     *   @SWG\Response(response=404, description="Заявка не найдена"),
     *   @SWG\Response(response=422, description="Заявка уже в этом статусе"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён"),
     *   @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionSetCancelled(int $id)
    {
        return $this->changeStatusAndNotify($id, CustomerRequestEntity::STATUS_CANCELLED, 'cancelled');
    }



    /**
     * Универсальный метод смены статуса с уведомлением клиента.
     *
     * @param int $id ID заявки
     * @param string $newStatus Новый статус (константа CustomerRequestEntity::STATUS_*)
     * @param string $template Имя шаблона письма (processing | completed | cancelled)
     * @return CustomerRequestEntity
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     * @throws ServerErrorHttpException
     */
    private function changeStatusAndNotify(int $id, string $newStatus, string $template): CustomerRequestEntity
    {
        $this->checkAccess();

        /** @var CustomerRequestEntity|null $model */
        $model = CustomerRequestEntity::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException("Заявка #{$id} не найдена.");
        }

        if ($model->status === $newStatus) {
            throw new UnprocessableEntityHttpException("Заявка #{$id} уже находится в статусе '{$newStatus}'.");
        }

        // Опционально: добавляем заметку менеджера из тела запроса
        $adminNotes = Yii::$app->request->getBodyParam('admin_notes');
        if ($adminNotes !== null && is_string($adminNotes)) {
            $model->admin_notes = trim($adminNotes);
        }

        // Меняем статус (updated_at обновится автоматически в beforeSave)
        $model->status = $newStatus;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                throw new \Exception('Не удалось сохранить заявку: ' . json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Ошибка смены статуса заявки #{$id}: " . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Не удалось изменить статус заявки.');
        }

        // Отправляем письмо клиенту (если указан email)
        $this->sendStatusEmail($model, $template);

        return $model;
    }


    /**
     * Отправляет email-уведомление клиенту о смене статуса заявки.
     *
     * @param CustomerRequestEntity $model
     * @param string $template processing | completed | cancelled
     */
    private function sendStatusEmail(CustomerRequestEntity $model, string $template): void
    {
        if (empty($model->email)) {
            Yii::info("Заявка #{$model->id}: email не указан, уведомление не отправлено.", __METHOD__);
            return;
        }

        try {
            $senderEmail = Yii::$app->params['senderEmail'] ?? 'noreply@specavtosila.ru';
            $senderName = Yii::$app->params['senderName'] ?? 'СПЕЦАВТОСИЛА';

            // Темы писем
            $subjects = [
                'processing' => "Ваша заявка №{$model->id} принята в работу",
                'completed' => "Ваш заказ №{$model->id} готов",
                'cancelled' => "Заявка №{$model->id} отменена",
            ];
            $subject = $subjects[$template] ?? "Обновление по заявке №{$model->id}";

            Yii::$app->mailer->compose(
                ['html' => "request/{$template}", 'text' => "request/{$template}-text"],
                ['model' => $model]
            )
                ->setFrom([$senderEmail => $senderName])
                ->setTo($model->email)
                ->setSubject($subject)
                ->send();

            Yii::info("Email '{$template}' отправлен на {$model->email} для заявки #{$model->id}", __METHOD__);
        } catch (\Exception $e) {
            // Ошибка отправки письма НЕ должна ломать основной сценарий
            Yii::warning("Не удалось отправить email '{$template}' для заявки #{$model->id}: " . $e->getMessage(), __METHOD__);
        }
    }
}
