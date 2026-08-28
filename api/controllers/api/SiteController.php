<?php

namespace app\controllers\api;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\web\Response;

/**
 * Site controller for basic API functionality.
 * This controller extends from yii\rest\Controller because it does not represent a model.
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 86400,
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['hello'], // only apply to the 'hello' action
                'rules' => [
                    [
                        'actions' => ['hello'],
                        'allow' => true,
                        'roles' => ['?'], // allow guests (unauthenticated users)
                    ],
                ],
            ],
        ];
    }

    /**
     * Responds to GET /api/hello with a welcome message and current date.
     * @return array
     */
    public function actionHello()
    {
        $date = Yii::$app->formatter->asDate(time(), 'dd MMMM yyyy');
        return [
            'success' => true,
            'message' => "Привет мир! Сервис работает. Сегодня {$date}. Хорошего дня"
        ];
    }

    /**
     * Handle OPTIONS requests for CORS preflight.
     * This is necessary for browsers to allow cross-origin requests with methods like POST, PUT, DELETE.
     * @return Response
     */
    public function actionOptions()
    {
        Yii::$app->getResponse()->setStatusCode(200);
        return [];
    }
}
