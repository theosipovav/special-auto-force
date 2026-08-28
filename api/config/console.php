<?php

$main = require __DIR__ . '/main.php';

// В консоли отключаем schema cache, чтобы не требовать компонент cache при миграциях
// (можно оставить включённым — у нас cache есть)
if (isset($main['components']['db']['enableSchemaCache'])) {
    $main['components']['db']['enableSchemaCache'] = false;
}

return yii\helpers\ArrayHelper::merge($main, [
    'id' => 'app-console',
    'name' => 'СПЕЦАВТОСИЛА REST API Console',
    'controllerNamespace' => 'app\commands',
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'db' => 'db',
            'migrationPath' => '@app/migrations',
            // 'migrationNamespaces' => ['app\migrations'], // если используете namespaces
        ],
    ],
]);