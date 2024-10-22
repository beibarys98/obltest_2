<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var common\models\TestSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Тесттер');
?>
<div class="test-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Жаңа тест'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-hover table-bordered'],
        'rowOptions' => function($model) {
            return [
                'onclick' => 'window.location.href="' . Url::to(['view', 'id' => $model->id]) . '";',
                'style' => 'cursor:pointer;',
            ];
        },
        'columns' => [
            'id',
            [
                'attribute' => 'subject',
                'label' => 'Пән',
                'value' => 'subject.title'
            ],
            [
                'attribute' => 'title',
                'label' => 'Атауы',
            ],
            [
                'attribute' => 'language',
                'label' => 'Тіл',
            ],
            [
                'attribute' => 'version',
                'label' => 'Нұсқа'
            ],
            [
                'attribute' => 'status',
                'label' => 'Статус'
            ],
            [
                'attribute' => 'duration',
                'label' => 'Ұзақтығы'
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
