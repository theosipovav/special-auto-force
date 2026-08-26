<?php

namespace app\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель сущности "Параметры сайта"
 *
 * @property int $id Id
 * @property string $title Название (Title)
 * @property string $value Значение (Value)
 * @property string|null $code Системный код (Code)
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
            'id' => 'ID',
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

    /**
     * Получить карту всех параметров в виде [code => value]
     */
    public static function getMap(): array
    {
        $rows = static::find()->asArray()->all();
        $map = [];
        foreach ($rows as $row) {
            $key = !empty($row['code']) ? $row['code'] : $row['title'];
            $map[$key] = $row['value'];
        }
        return $map;
    }
}
