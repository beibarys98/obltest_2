<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\TestResult]].
 *
 * @see \common\models\TestResult
 */
class TestResultQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\TestResult[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\TestResult|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
