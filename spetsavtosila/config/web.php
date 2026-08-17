<?php

return [
    'id' => 'spetsavtosila',
    'name' => 'СПЕЦАВТОСИЛА',
    'language' => 'ru',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'spetsavtosila-secret-key-change-in-production',
            'baseUrl' => '',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=spetsavtosila',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'tablePrefix' => '',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'catalog' => 'category/index',
                'category/<id:\d+>' => 'category/view',
                'product/<id:\d+>' => 'product/view',
                'search' => 'site/search',
                'admin' => 'admin/dashboard',
                'admin/<controller:[\w\-]+>' => 'admin/<controller>/index',
                '<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>',
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\AdminModule',
        ],
    ],
    'params' => require __DIR__ . '/params.php',
];
