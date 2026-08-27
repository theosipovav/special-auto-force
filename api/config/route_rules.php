<?php

return [

    // WEB

    'GET docs' => 'docs/index',
    'GET docs/json-schema' => 'docs/json-schema',

    // ===== ПУБЛИЧНОЕ API =====

    'GET hello' => 'api/site/hello',


    // = ПУБЛИЧНЫЕ ====================
    // --- Аутентификация ---
    'POST auth/login' => 'api/auth/login',
    'POST auth/signup' => 'api/auth/signup',
    'GET auth/me' => 'api/auth/me',
    'POST auth/refresh' => 'api/auth/refresh',
    // === Параметры сайта ============
    'GET parameters' => 'api/parameter/index',
    'GET parameter/find-by-code/<code>' => 'api/parameter/find-by-code',
    // === Категории ==================
    'GET categories' => 'api/category/index',
    // === Продукция ==================
    'GET products/latest'  => 'api/product/latest',
    'GET products/popular' => 'api/product/popular',
    'GET products/search'  => 'api/product/search',
    'GET products'         => 'api/product/index',

    // = ПАНЕЛЬ АДМИНИСТРИРОВАНИЯ =====
    // === Изображения ================
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
    // === Заявки =====================
    'GET    /admin/requests' => 'api/admin/request/index',
    'GET    /admin/request/<id:\d+>' => 'api/admin/request/view',
    'PUT    /admin/request/<id:\d+>' => 'api/admin/request/update',
    'DELETE /admin/request/<id:\d+>' => 'api/admin/request/delete',
    'POST   /admin/request/<id:\d+>/set-processing' => 'api/admin/request/set-processing',
    'POST   /admin/request/<id:\d+>/set-completed' => 'api/admin/request/set-completed',
    'POST   /admin/request/<id:\d+>/set-cancelled' => 'api/admin/request/set-cancelled',
    // === Роли ========================
    'GET /admin/roles' => 'api/admin/role/index',
    // === Пользователи ================
    'GET /admin/users' => 'api/admin/user/index',
    'GET /admin/user/<id:\d+>' => 'api/admin/user/view',
    'DELETE /admin/user/<id:\d+>' => 'api/admin/user/delete',
    'POST /admin/user' => 'api/admin/user/create',
    'PUT /admin/user/<id:\d+>' => 'api/admin/user/update',
    'POST /admin/user/<id:\d+>/password-set' => 'api/admin/user/password-set',
    'POST /admin/user/<id:\d+>/disabled' => 'api/admin/user/disabled',
    'POST /admin/user/<id:\d+>/assign-role' => 'api/admin/user/assign-role',
    'DELETE /admin/user/<id:\d+>/roles/<roleId:\d+>' => 'api/admin/user/revoke-role',
    // === Параметры сайта ============
    'GET admin/parameters' => 'api/admin/parameter/index',
    'GET admin/parameters/<id:\d+>' => 'api/admin/parameter/view',
    'PUT admin/parameters/<id:\d+>' => 'api/admin/parameter/update',
    'PATCH admin/parameters/<id:\d+>' => 'api/admin/parameter/update',
    'DELETE admin/parameters/<id:\d+>' => 'api/admin/parameter/delete',
    'OPTIONS admin/parameters' => 'api/admin/parameter/options',
    'OPTIONS admin/parameters/<id:\d+>' => 'api/admin/parameter/options',



];
