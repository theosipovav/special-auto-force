<?php

$main = require __DIR__ . '/main.php';

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
            'identityClass' => 'app\models\entities\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'baseUrl' => '/api',
            'rules' => [

                // WEB

                'GET docs' => 'docs/index',
                'GET docs/json-schema' => 'docs/json-schema',

                // ===== ПУБЛИЧНОЕ API =====

                'GET hello' => 'api/site/hello',

                // --- Аутентификация ---
                'POST auth/login' => 'api/auth/login',
                'POST auth/signup' => 'api/auth/signup',
                'GET auth/me' => 'api/auth/me',
                'POST auth/refresh' => 'api/auth/refresh',


                // --- Продукция ---
                'GET products'           => 'api/product/index',
                'GET products/latest'    => 'api/product/latest',
                'GET products/popular'   => 'api/product/popular',
                'GET products/search'    => 'api/product/search',

                'GET categories/<id:\d+>/products' => 'api/category/products',
                'GET categories/tree' => 'api/category/tree',

                
                'GET parameters/map' => 'api/parameter/map',

                // ===== ПАНЕЛЬ АДМИНИСТРИРОВАНИЯ =====

                'POST   admin/products/<id:\d+>/sync-categories'   => 'api/admin/product/sync-categories',
                'POST   admin/products/<id:\d+>/images'            => 'api/admin/product/add-image',

                // --- CRUD (RESTful) ---
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'users' => 'api/user',
                        'roles' => 'api/role',
                        'categories' => 'api/category',
                        'admin/products' => 'api/admin/product',
                        'product-images' => 'api/product-image',
                        'parameters' => 'api/parameter',
                        'requests' => 'api/request',
                        'admin/image' => 'api/admin/image',
                    ],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'OPTIONS <action>' => 'options',
                    ],
                ],
            ],
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
