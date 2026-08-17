<?php
/**
 * Composer autoload file (simulated for project structure)
 * In production, run: composer install
 */

// Yii2 and dependencies would be loaded via Composer
// This is a placeholder for the project entry point

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';

(new yii\web\Application($config))->run();
