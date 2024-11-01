<?php

use common\models\Subject;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var common\models\SubjectSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Пән');
?>
<div class="subject-index">

    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Жаңа пән'), ['create'], ['class' => 'btn btn-success w-100']) ?>
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
                    'attribute' => 'title',
                    'label' => 'Атауы'
            ],
            [
                    'attribute' => 'title_ru',
                    'label' => 'Название'
            ],
            [
                'class' => ActionColumn::className(),
                'template' => '{delete}',
                'urlCreator' => function ($action, Subject $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
