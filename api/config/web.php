<?php

$main = require __DIR__ . '/main.php';
$routeRules = require __DIR__ . '/route_rules.php';

$config = yii\helpers\ArrayHelper::merge($main, [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'spetsavtosila_rest_api_secret_cookie_key_2026',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'formatters' => [
                yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            },
        ],
        'user' => [
            'identityClass' => 'app\entities\UserEntity',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'baseUrl' => '/api',
            'rules' => $routeRules,
        ],
        'assetManager' => [
            'basePath' => '@app/web/assets',
            'baseUrl'  => '/api/web/assets',
            'bundles' => [
                'yii2mod\swagger\SwaggerAsset' => [
                    'sourcePath' => null,
                    'baseUrl'    => 'https://unpkg.com/swagger-ui-dist@3.52.5',
                    'js' => [
                        'swagger-ui-bundle.js',
                        'swagger-ui-standalone-preset.js',
                    ],
                    'css' => [
                        'swagger-ui.css',
                    ],
                ],
            ],
        ],
    ],
]);

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;
