<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TeacherSearch represents the model behind the search form of `common\models\Teacher`.
 */
class TeacherSearch extends Teacher
{
    public $subject;
    public $test;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'subject_id', 'test_id', 'result'], 'integer'],
            [['name', 'school', 'language', 'start_time', 'end_time', 'subject', 'test'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Teacher::find();

        // add conditions that should always apply here

        $query->joinWith(['subject']);
        $query->joinWith(['test']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'name',
                    'school',
                    'subject',
                    'test',
                    'language' => [
                        'asc' => ['teacher.language' => SORT_ASC],
                        'desc' => ['teacher.language' => SORT_DESC],
                    ],
                    'start_time',
                    'end_time',
                    'result',
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!empty($params['id'])) {
            $query->andFilterWhere(['test_id' => $params['id']]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'test_id' => $this->test_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'result' => $this->result
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'school', $this->school])
            ->andFilterWhere(['like', 'teacher.language', $this->language])

            ->andFilterWhere(['like', 'subject.title', $this->subject])
            ->andFilterWhere(['like', 'test.title', $this->test]);

        return $dataProvider;
    }
}
