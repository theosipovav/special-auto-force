<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=mysql;port=3306;dbname=app;charset=utf8mb4',
    'username' => 'app',
    'password' => 'ChangeThisAppPassword_123!',
    'charset' => 'utf8mb4',

    // Production schema cache
    'enableSchemaCache' => YII_ENV_PROD,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
