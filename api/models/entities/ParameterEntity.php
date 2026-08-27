<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Параметры сайта"
 *
 * @SWG\Definition(
 *     definition="ParameterEntity",
 *     type="object",
 *     required={"id", "title", "value"},
 *     @SWG\Property(property="id", type="integer", description="Идентификатор записи"),
 *     @SWG\Property(property="title", type="string", description="Название параметра", maxLength=255),
 *     @SWG\Property(property="value", type="string", description="Значение"),
 *     @SWG\Property(property="code", type="string", description="Системный код", maxLength=64),
 *     @SWG\Property(property="group", type="string", description="Группа параметров", maxLength=64),
 *     @SWG\Property(property="pageId", type="integer", description="Идентификатор страницы")
 * )
 *
 * @property int $id Идентификатор записи 
 * @property string $title Название параметра
 * @property string $value Значение
 * @property string|null $code Системный код
 * @property string|null $group Группа параметров
 * @property int|null $pageId Идентификатор страницы
 *
 * @property PageEntity|null $page
 */
class ParameterEntity extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%parameter}}';
    }

    public function rules()
    {
        return [
            [['title', 'value'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            [['value'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 64],
            [['group'], 'string', 'max' => 64],
            [['pageId'], 'integer'],
            [['pageId'], 'exist', 'skipOnError' => true, 'targetClass' => PageEntity::class, 'targetAttribute' => ['pageId' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи',
            'title' => 'Название параметра',
            'value' => 'Значение',
            'code' => 'Системный код',
            'group' => 'Группа',
            'pageId' => 'Идентификатор страницы',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'value',
            'code',
            'group',
            'pageId',
        ];
    }

    /**
     * Gets query for [[PageEntity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(PageEntity::class, ['id' => 'pageId']);
    }

    /**
     * Получить значение параметра по системному коду или названию
     */
    public static function getValue(string $codeOrTitle, $default = null)
    {
        $param = static::find()
            ->where(['code' => $codeOrTitle])
            ->orWhere(['title' => $codeOrTitle])
            ->one();

        return $param ? $param->value : $default;
    }
}
