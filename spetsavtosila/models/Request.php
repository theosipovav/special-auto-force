<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Request model (Заявка)
 */
class Request extends ActiveRecord
{
    public static function tableName()
    {
        return 'request';
    }

    public function rules()
    {
        return [
            [['product_id', 'phone', 'email'], 'required'],
            [['product_id', 'user_id'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'email', 'max' => 128],
            [['wishes'], 'string'],
            [['status'], 'string', 'max' => 32],
            [['created_at', 'updated_at'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Товар',
            'user_id' => 'Пользователь',
            'phone' => 'Телефон',
            'email' => 'Email',
            'wishes' => 'Пожелания',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Обновлено',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
                $this->status = 'new';
            }
            $this->updated_at = time();
            return true;
        }
        return false;
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function sendNotification()
    {
        $adminEmail = \Yii::$app->params['adminEmail'];
        $senderEmail = \Yii::$app->params['senderEmail'];
        
        return \Yii::$app->mailer->compose()
            ->setTo($adminEmail)
            ->setFrom([$senderEmail => \Yii::$app->params['senderName']])
            ->setSubject('Новая заявка на товар: ' . $this->product->title)
            ->setTextBody("Новая заявка!\n\nТовар: {$this->product->title}\nТелефон: {$this->phone}\nEmail: {$this->email}\nПожелания: {$this->wishes}")
            ->send();
    }
}
