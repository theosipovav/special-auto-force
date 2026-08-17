<?php

namespace app\modules\admin\controllers;

use app\models\ProductImage;
use app\models\Product;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ProductImageController implements the CRUD actions for ProductImage model.
 */
class ProductImageController extends AdminController
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

    public function actionIndex($productId)
    {
        $product = Product::findOne($productId);
        if (!$product) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => ProductImage::find()->where(['product_id' => $productId]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'product' => $product,
        ]);
    }

    public function actionCreate($productId)
    {
        $model = new ProductImage();
        $model->product_id = $productId;

        if ($model->load(\Yii::$app->request->post())) {
            $model->image = UploadedFile::getInstance($model, 'image');
            if ($model->image) {
                $fileName = time() . '_' . $model->image->baseName . '.' . $model->image->extension;
                $model->image->saveAs(\Yii::getAlias('@webroot/images/products/') . $fileName);
                $model->image = '/images/products/' . $fileName;
                
                if ($model->save()) {
                    return $this->redirect(['/admin/product/view', 'id' => $productId]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(\Yii::$app->request->post())) {
            $model->image = UploadedFile::getInstance($model, 'image');
            if ($model->image) {
                $fileName = time() . '_' . $model->image->baseName . '.' . $model->image->extension;
                $model->image->saveAs(\Yii::getAlias('@webroot/images/products/') . $fileName);
                $model->image = '/images/products/' . $fileName;
            }
            
            if ($model->save(false)) {
                return $this->redirect(['/admin/product/view', 'id' => $model->product_id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $productId = $model->product_id;
        $model->delete();

        return $this->redirect(['/admin/product/view', 'id' => $productId]);
    }

    protected function findModel($id)
    {
        if (($model = ProductImage::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}
