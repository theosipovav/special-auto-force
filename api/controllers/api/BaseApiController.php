<?php

namespace app\controllers\api;

use yii\rest\ActiveController;
use yii\filters\Cors;
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
                    'X-Pagination-Current-PageEntity',
                    'X-Pagination-PageEntity-Count',
                    'X-Pagination-Per-PageEntity',
                    'X-Pagination-Total-Count',
                ],
            ],
        ];
        return $behaviors;
    }

}
