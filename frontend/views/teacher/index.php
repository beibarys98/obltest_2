<?php

use common\models\Teacher;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var common\models\TeacherSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Мұғалімдер');
?>
<div class="teacher-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Жаңа мұғалім'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-hover table-bordered'],
        'rowOptions' => function($model) {
            return [
                'onclick' => 'window.location.href="' . Url::to(['update', 'id' => $model->id]) . '";',
                'style' => 'cursor:pointer;',
            ];
        },
        'columns' => [
            'id',
            [
                    'attribute' => 'name',
                    'label' => 'Есім'
            ],
            [
                    'attribute' => 'school',
                    'label' => 'Мекеме'
            ],
            [
                    'attribute' => 'subject',
                    'label' => 'Пән',
                    'value' => 'subject.title'
            ],
            [
                    'attribute' => 'test',
                    'label' => 'Тест',
                    'value' => 'test.title'
            ],
            [
                    'attribute' => 'language',
                    'label' => 'Тіл'
            ],
            [
                    'attribute' => 'start_time',
                    'label' => 'Бастады'
            ],
            [
                    'attribute' => 'end_time',
                    'label' => 'Аяқтады'
            ],
            [
                'class' => ActionColumn::className(),
                'template' => '{delete}',
                'urlCreator' => function ($action, Teacher $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
