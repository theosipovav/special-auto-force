<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Product;
use app\models\Request;
use yii\web\NotFoundHttpException;

/**
 * Product controller
 */
class ProductController extends Controller
{
    public function actionView($id)
    {
        $product = Product::findOne($id);
        if (!$product) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        // Increment views
        $product->incrementViews();

        // Get other products from same category
        $categoryIds = array_column($product->categories, 'id');
        $relatedProductsQuery = Product::find()
            ->innerJoin('product_category', 'product_category.product_id = product.id')
            ->where(['product_category.category_id' => $categoryIds])
            ->andWhere(['!=', 'product.id', $product->id])
            ->limit(10);

        return $this->render('view', [
            'product' => $product,
            'relatedProducts' => $relatedProductsQuery->all(),
        ]);
    }

    public function actionRequest()
    {
        $model = new Request();

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            // Send notification to admin
            $model->sendNotification();
            
            \Yii::$app->session->setFlash('success', 'Ваша заявка успешно отправлена. Менеджер свяжется с вами в ближайшее время.');
            return $this->redirect(['/product/view', 'id' => $model->product_id]);
        }

        return $this->redirect(['/']);
    }
}
