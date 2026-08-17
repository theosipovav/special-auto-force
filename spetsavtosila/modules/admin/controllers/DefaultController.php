<?php

namespace app\modules\admin\controllers;

use yii\web\Controller;

/**
 * Default controller for admin module - Dashboard
 */
class DefaultController extends AdminController
{
    public function actionIndex()
    {
        $usersCount = \app\models\User::find()->count();
        $productsCount = \app\models\Product::find()->count();
        $categoriesCount = \app\models\Category::find()->count();
        $requestsCount = \app\models\Request::find()->where(['status' => 'new'])->count();
        
        $recentRequests = \app\models\Request::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'usersCount' => $usersCount,
            'productsCount' => $productsCount,
            'categoriesCount' => $categoriesCount,
            'requestsCount' => $requestsCount,
            'recentRequests' => $recentRequests,
        ]);
    }
}
