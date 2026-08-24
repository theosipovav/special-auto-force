<?php

namespace app\controllers\api;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;
use app\models\entities\CustomerRequest;
use app\models\entities\Product;
use app\models\entities\Parameter;
use app\models\entities\User;

/**
 * REST API контроллер заявок клиентов (CustomerRequest)
 */
class RequestController extends BaseApiController
{
    public $modelClass = CustomerRequest::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Protect index, view, update, delete for managers and admins. 'create' is public!
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['index', 'view', 'update', 'delete'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Custom provider with status filter
        $actions['index']['prepareDataProvider'] = function () {
            $query = CustomerRequest::find()->with('product');

            $status = Yii::$app->request->get('status');
            if (!empty($status)) {
                $query->andWhere(['status' => $status]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        };

        // Override create action to auto-increment orders_count and send email notification
        $actions['create'] = [
            'class' => 'yii\rest\CreateAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario' => $this->createScenario,
            'afterSave' => function ($model) {
                /** @var CustomerRequest $model */
                if ($model->product_id) {
                    Product::updateAllCounters(['orders_count' => 1], ['id' => $model->product_id]);
                }

                // Email notification to administrator
                try {
                    $adminEmail = Parameter::getValue('site_order_email', Yii::$app->params['adminEmail'] ?? 'admin@specavtosila.ru');
                    Yii::$app->mailer->compose()
                        ->setFrom([Yii::$app->params['senderEmail'] ?? 'noreply@specavtosila.ru' => 'СПЕЦАВТОСИЛА'])
                        ->setTo($adminEmail)
                        ->setSubject("Новая заявка #{$model->id} от {$model->phone}")
                        ->setTextBody("Поступила новая заявка на сайте.\nТелефон: {$model->phone}\nEmail: {$model->email}\nТовар ID: {$model->product_id}\nТекст: {$model->wishlist}")
                        ->send();
                } catch (\Exception $e) {
                    Yii::warning('Не удалось отправить Email о новой заявке: ' . $e->getMessage());
                }
            },
        ];

        return $actions;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['index', 'view', 'update', 'delete'])) {
            /** @var User $currentUser */
            $currentUser = Yii::$app->user->identity;
            if (!$currentUser) {
                throw new ForbiddenHttpException('Требуется авторизация.');
            }
            if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('manager') && !$currentUser->hasRole('Администратор')) {
                throw new ForbiddenHttpException('Доступ к заявкам разрешен только менеджерам и администраторам.');
            }
        }
    }
}
