<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "test".
 *
 * @property int $id
 * @property int|null $subject_id
 * @property string $title
 * @property string|null $path
 * @property string|null $language
 * @property int|null $version
 * @property string|null $status
 * @property string|null $duration
 *
 * @property Subject $subject
 * @property Teacher[] $teachers
 */
class Test extends \yii\db\ActiveRecord
{
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'docx'],

            [['subject_id', 'path', 'language', 'version', 'duration'], 'required'],
            [['subject_id', 'version'], 'integer'],
            [['duration'], 'safe'],
            [['path'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10],
            [['status'], 'string', 'max' => 50],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subject::class, 'targetAttribute' => ['subject_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'subject_id' => Yii::t('app', 'Subject ID'),
            'title' => Yii::t('app', 'Title'),
            'path' => Yii::t('app', 'Path'),
            'language' => Yii::t('app', 'Language'),
            'version' => Yii::t('app', 'Version'),
            'status' => Yii::t('app', 'Status'),
            'duration' => Yii::t('app', 'Duration'),
        ];
    }

    /**
     * Gets query for [[Subject]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\SubjectQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    /**
     * Gets query for [[Teachers]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\TeacherQuery
     */
    public function getTeachers()
    {
        return $this->hasMany(Teacher::class, ['test_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\TestQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\TestQuery(get_called_class());
    }
}
