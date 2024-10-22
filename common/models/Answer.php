<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "answer".
 *
 * @property int $id
 * @property int $question_id
 * @property string $content
 * @property string|null $formula_path
 *
 * @property Question $question
 * @property TeacherAnswer[] $teacherAnswers
 */
class Answer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['question_id', 'content'], 'required'],
            [['question_id'], 'integer'],
            [['content'], 'string'],
            [['formula_path'], 'string', 'max' => 255],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => Question::class, 'targetAttribute' => ['question_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'question_id' => Yii::t('app', 'Question ID'),
            'content' => Yii::t('app', 'Content'),
            'formula_path' => Yii::t('app', 'Formula Path'),
        ];
    }

    /**
     * Gets query for [[Question]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\QuestionQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(Question::class, ['id' => 'question_id']);
    }

    /**
     * Gets query for [[TeacherAnswers]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\TeacherAnswerQuery
     */
    public function getTeacherAnswers()
    {
        return $this->hasMany(TeacherAnswer::class, ['answer_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\AnswerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\AnswerQuery(get_called_class());
    }
}
