<?php

return [

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

    // = ПАНЕЛЬ АДМИНИСТРИРОВАНИЯ =====
    // === Изображегия ================
    'GET    admin/images' => 'api/admin/image/index',
    'POST   admin/image' => 'api/admin/image/create',
    'DELETE admin/image/<id:\d+>' => 'api/admin/image/delete',
    // === Категория ==================
    'POST   admin/category' => 'api/admin/category/create',
    'PUT    admin/category/<id:\d+>' => 'api/admin/category/update',
    'DELETE admin/category/<id:\d+>' => 'api/admin/category/delete',
    // === Продукция ==================
    'POST   admin/product' => '/api/admin/product/create',
    'PUT    admin/product/<id:\d+>' => '/api/admin/product/update',
    'POST   admin/product/<id:\d+>/sync-images' => '/api/admin/product/sync-images',
    'POST   admin/product/<id:\d+>/sync-categories' => '/api/admin/product/sync-categories',
    'DELETE admin/product/<id:\d+>' => '/api/admin/product/delete',


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
        ],
        'pluralize' => false,
        'extraPatterns' => [
            'OPTIONS <action>' => 'options',
        ],
    ],
];
