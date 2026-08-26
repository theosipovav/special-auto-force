<?php

namespace app\controllers\api;

use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\CompositeAuth;
use yii\web\Response;

/**
 * Базовый абстрактный REST API контроллер
 */
abstract class BaseApiController extends ActiveController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Current-Page',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Per-Page',
                    'X-Pagination-Total-Count',
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * Helper для настройки Bearer JWT аутентификации на защищенных действиях
     */
    protected function applyBearerAuth(array $onlyActions = [])
    {
        return [
            'authenticator' => [
                'class' => CompositeAuth::class,
                'only' => $onlyActions,
                'authMethods' => [
                    HttpBearerAuth::class,
                    [
                        'class' => QueryParamAuth::class,
                        'tokenParam' => 'access_token',
                    ],
                ],
            ],
        ];
    }

}
