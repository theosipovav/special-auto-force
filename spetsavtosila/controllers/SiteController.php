<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Product;
use app\models\Category;
use app\models\Request;
use app\models\Parameter;
use yii\data\ActiveDataProvider;

/**
 * Site controller - Main pages
 */
class SiteController extends Controller
{
    public function actionIndex()
    {
        // Latest products (10 items)
        $latestProducts = Product::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Most popular products (by orders_count)
        $popularProducts = Product::find()
            ->orderBy(['orders_count' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('index', [
            'latestProducts' => $latestProducts,
            'popularProducts' => $popularProducts,
        ]);
    }

    public function actionSearch()
    {
        $query = \Yii::$app->request->get('q', '');
        
        $productsQuery = Product::find();
        
        if (!empty($query)) {
            $productsQuery->andWhere(['or', 
                ['like', 'title', $query],
                ['like', 'short_description', $query],
                ['like', 'long_description', $query],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $productsQuery,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('search', [
            'dataProvider' => $dataProvider,
            'query' => $query,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionContact()
    {
        return $this->render('contact');
    }

    public function actionInfo()
    {
        return $this->render('info');
    }

    public function actionError()
    {
        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }
}
