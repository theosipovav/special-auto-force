<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Заявка клиента"
 * 
 * @SWG\Definition(definition="CustomerRequestEntity", required={"id", "product_id", "created_at", "updated_at", "status"},
 *     @SWG\Property(property="id", type="integer", description="Идентификатор записи"),
 *     @SWG\Property(property="product_id", type="integer", description="Идентификатор продукции"),
 *     @SWG\Property(property="phone", type="string", description="Телефон"),
 *     @SWG\Property(property="email", type="string", description="Адрес электронной почты"),
 *     @SWG\Property(property="wishlist", type="string", description="Пожелания / Текст заявки"),
 *     @SWG\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @SWG\Property(property="updated_at", type="string", format="date-time", description="Дата последнего обновления"),
 *     @SWG\Property(property="status", type="string", description="Статус"),
 *     @SWG\Property(property="admin_notes", type="string", description="Служебные заметки"),
 * )
 *
 * @property int $id Идентификатор записи
 * @property int $product_id Идентификатор продукции
 * @property string|null $phone Телефон
 * @property string|null $email Адрес электронной почты
 * @property string|null $wishlist Пожелания / Текст заявки
 * @property string $created_at Дата создания
 * @property string $updated_at Дата последнего обновления
 * @property string $status Статус (new, processing, completed, cancelled)
 * @property string|null $admin_notes Служебные заметки
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
        return '{{%customer_request}}';
    }

    public function rules()
    {
        return [
            [['product_id', 'status'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
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
            'product_id' => 'Идентификатор продукции',
            'phone' => 'Телефон',
            'email' => 'Адрес электронной почты',
            'wishlist' => 'Пожелания / Текст заявки',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата последнего обновления',
            'status' => 'Статус',
            'admin_notes' => 'Служебные заметки',
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
            'updatedAt' => 'updated_at',
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
            $now = date('Y-m-d H:i:s');
            if ($this->isNewRecord) {
                if (empty($this->created_at)) {
                    $this->created_at = $now;
                }
                $this->updated_at = $this->created_at;
            } else {
                $this->updated_at = $now;
            }
            return true;
        }
        return false;
    }
}
