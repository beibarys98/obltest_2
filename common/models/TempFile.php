<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "temp_file".
 *
 * @property int $id
 * @property int $test_id
 * @property string $path
 *
 * @property Test $test
 */
class TempFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['test_id', 'path'], 'required'],
            [['test_id'], 'integer'],
            [['path'], 'string', 'max' => 255],
            [['test_id'], 'exist', 'skipOnError' => true, 'targetClass' => Test::class, 'targetAttribute' => ['test_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'test_id' => Yii::t('app', 'Test ID'),
            'path' => Yii::t('app', 'Path'),
        ];
    }

    /**
     * Gets query for [[Test]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\TestQuery
     */
    public function getTest()
    {
        return $this->hasOne(Test::class, ['id' => 'test_id']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\TempFileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\TempFileQuery(get_called_class());
    }
}
