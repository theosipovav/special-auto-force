<?php

namespace app\modules\admin\controllers;

use app\models\Product;
use app\models\Category;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends AdminController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Product::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Product();

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                // Save categories
                $categories = \Yii::$app->request->post('Product')['categories'] ?? [];
                if (!empty($categories)) {
                    foreach ($categories as $categoryId) {
                        \Yii::$app->db->createCommand()
                            ->insert('product_category', ['product_id' => $model->id, 'category_id' => $categoryId])
                            ->execute();
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $categories = Category::find()->all();

        return $this->render('create', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                // Update categories
                \Yii::$app->db->createCommand()
                    ->delete('product_category', ['product_id' => $model->id])
                    ->execute();
                
                $categories = \Yii::$app->request->post('Product')['categories'] ?? [];
                if (!empty($categories)) {
                    foreach ($categories as $categoryId) {
                        \Yii::$app->db->createCommand()
                            ->insert('product_category', ['product_id' => $model->id, 'category_id' => $categoryId])
                            ->execute();
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $categories = Category::find()->all();
        $productCategories = \Yii::$app->db->createCommand(
            'SELECT category_id FROM product_category WHERE product_id = :productId'
        )
        ->bindValue(':productId', $model->id)
        ->queryColumn();

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
            'productCategories' => $productCategories,
        ]);
    }

    public function actionDelete($id)
    {
        \Yii::$app->db->createCommand()
            ->delete('product_category', ['product_id' => $id])
            ->execute();
        
        \Yii::$app->db->createCommand()
            ->delete('product_image', ['product_id' => $id])
            ->execute();
        
        \Yii::$app->db->createCommand()
            ->delete('order', ['product_id' => $id])
            ->execute();
        
        \Yii::$app->db->createCommand()
            ->delete('request', ['product_id' => $id])
            ->execute();
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}
