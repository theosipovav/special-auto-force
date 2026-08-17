<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Category;
use yii\data\ActiveDataProvider;

/**
 * Category controller
 */
class CategoryController extends Controller
{
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Category::find(),
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $category = Category::findOne($id);
        if (!$category) {
            throw new \yii\web\NotFoundHttpException('Категория не найдена.');
        }

        $productsQuery = $category->getProducts();
        
        $dataProvider = new ActiveDataProvider([
            'query' => $productsQuery,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('view', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }
}
