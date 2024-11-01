<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "teacher".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $school
 * @property int|null $subject_id
 * @property int|null $test_id
 * @property string|null $language
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int|null $result
 *
 * @property Subject $subject
 * @property Test $test
 */
class Teacher extends \yii\db\ActiveRecord
{
    public $password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'teacher';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'school'], 'required'],
            [['user_id', 'subject_id', 'test_id', 'result'], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['name', 'school'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subject::class, 'targetAttribute' => ['subject_id' => 'id']],
            [['test_id'], 'exist', 'skipOnError' => true, 'targetClass' => Test::class, 'targetAttribute' => ['test_id' => 'id']],

            ['name', 'match', 'pattern' => '/^[А-ЯЁӘІҢҒҮҰҚӨҺа-яёәіңғүұқөһ\s]+$/u', 'message' => Yii::t('app', 'Имя может содержать только кириллицу!')],
            ['name', 'match', 'pattern' => '/^[^\s]/', 'message' => Yii::t('app', 'Имя не может начинаться с пробела!')],
            ['name', 'match', 'pattern' => '/\s/', 'message' => Yii::t('app', 'Имя должно содержать минимум два слова!')],

            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'school' => Yii::t('app', 'School'),
            'subject_id' => Yii::t('app', 'Subject ID'),
            'test_id' => Yii::t('app', 'Test ID'),
            'language' => Yii::t('app', 'Language'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
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
     * @return \common\models\query\TeacherQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\TeacherQuery(get_called_class());
    }
}
