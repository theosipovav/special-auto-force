<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Заявка клиента"
 *
 * @property int $id Id
 * @property int|null $product_id ProductId
 * @property string $phone Телефон (Phone)
 * @property string $email Адрес электронной почты (Email)
 * @property string|null $wishlist Пожелания / Текст заявки (Wishlist)
 * @property string $created_at Дата создания (CreatedAt)
 * @property string $status Статус (new, processing, completed, cancelled)
 * @property string|null $admin_notes Служебные заметки менеджера
 *
 * @property ProductEntity|null $product
 */
class CustomerRequestEntity extends ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function tableName()
    {
        return '{{%request}}';
    }

    public function rules()
    {
        return [
            [['phone', 'email'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            ['email', 'email', 'message' => 'Некорректный формат адреса электронной почты'],
            [['product_id'], 'integer'],
            [['wishlist', 'admin_notes'], 'string'],
            [['phone'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 191],
            [['status'], 'string', 'max' => 32],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['created_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductEntity::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи',
            'product_id' => 'Товар',
            'phone' => 'Телефон',
            'email' => 'Электронная почта',
            'wishlist' => 'Пожелания к заказу / Текст',
            'created_at' => 'Дата создания',
            'status' => 'Статус обработки',
            'admin_notes' => 'Заметки администратора',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'productId' => 'product_id',
            'productTitle' => function () {
                return $this->product ? $this->product->title : null;
            },
            'phone',
            'email',
            'wishlist',
            'createdAt' => 'created_at',
            'status',
            'adminNotes' => 'admin_notes',
            'product' => function () {
                if (!$this->product) return null;
                return [
                    'id' => $this->product->id,
                    'title' => $this->product->title,
                    'article' => $this->product->article,
                    'price' => $this->product->price,
                ];
            },
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(ProductEntity::class, ['id' => 'product_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord && empty($this->created_at)) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }
}
