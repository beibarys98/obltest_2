<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "percentage".
 *
 * @property int $id
 * @property int $first
 * @property int $second
 * @property int $third
 * @property int $fourth
 * @property int $fifth
 */
class Percentage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'percentage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first', 'second', 'third', 'fourth', 'fifth'], 'required'],
            [['first', 'second', 'third', 'fourth', 'fifth'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'first' => Yii::t('app', 'First'),
            'second' => Yii::t('app', 'Second'),
            'third' => Yii::t('app', 'Third'),
            'fourth' => Yii::t('app', 'Fourth'),
            'fifth' => Yii::t('app', 'Fifth'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\PercentageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\PercentageQuery(get_called_class());
    }
}
