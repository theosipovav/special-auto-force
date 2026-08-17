<?php

namespace app\modules\admin\controllers;

use app\models\User;
use app\models\Role;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminController
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
            'query' => User::find(),
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
        $model = new User();

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->password_hash) {
                $model->setPassword($model->password_hash);
            }
            $model->generateAuthKey();
            $model->created_at = time();
            $model->updated_at = time();
            
            if ($model->save()) {
                // Save roles
                $roles = \Yii::$app->request->post('User')['roles'] ?? [];
                if (!empty($roles)) {
                    foreach ($roles as $roleId) {
                        \Yii::$app->db->createCommand()
                            ->insert('user_role', ['user_id' => $model->id, 'role_id' => $roleId])
                            ->execute();
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $roles = Role::find()->all();

        return $this->render('create', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->password_hash) {
                $model->setPassword($model->password_hash);
            }
            $model->updated_at = time();
            
            if ($model->save()) {
                // Update roles
                \Yii::$app->db->createCommand()
                    ->delete('user_role', ['user_id' => $model->id])
                    ->execute();
                
                $roles = \Yii::$app->request->post('User')['roles'] ?? [];
                if (!empty($roles)) {
                    foreach ($roles as $roleId) {
                        \Yii::$app->db->createCommand()
                            ->insert('user_role', ['user_id' => $model->id, 'role_id' => $roleId])
                            ->execute();
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $roles = Role::find()->all();
        $userRoles = \Yii::$app->db->createCommand(
            'SELECT role_id FROM user_role WHERE user_id = :userId'
        )
        ->bindValue(':userId', $model->id)
        ->queryColumn();

        return $this->render('update', [
            'model' => $model,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function actionDelete($id)
    {
        \Yii::$app->db->createCommand()
            ->delete('user_role', ['user_id' => $id])
            ->execute();
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}
