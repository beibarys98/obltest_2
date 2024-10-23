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

/** @var $test */

$this->title = Yii::t('app', 'Қатысушылар');
?>
<div class="teacher-index">

    <div class="d-flex">
        <div style="width: 70%;">

        </div>
        <div style="width: 30%;" >
            <div style="border: 1px solid black; border-radius: 10px;" class="p-3">

                <h1><?= $test->title ?></h1>

                <div class="row">
                    <div class="col-4 d-flex justify-content-center">
                        <?php
                        if($test->status == 'new'){
                            echo Html::a(Yii::t('app', 'Дайын') ,
                                ['/test/ready', 'id' => $test->id],
                                ['class' => 'btn btn-success']);
                        }else if($test->status == 'ready'){
                            echo Html::a(Yii::t('app', 'Жариялау'),
                                ['test/publish', 'id' => $test->id],
                                [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                                    ]
                                ]);
                        }else if($test->status == 'public'){
                            echo Html::a(Yii::t('app', 'Аяқтау') ,
                                ['test/end', 'id' => $test->id],
                                [
                                    'class' => 'btn btn-warning',
                                    'data' => [
                                        'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                                    ]
                                ]);
                        }else if($test->status == 'finished'){
                            echo Html::a(Yii::t('app', 'Марапаттау') ,
                                ['test/present', 'id' => $test->id],
                                [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                                    ]
                                ]);
                        }else if($test->status == 'certificated'){
                            echo Html::a(Yii::t('app', 'Нәтиже') ,
                                ['/test/result', 'id' => $test->id],
                                ['class' => 'btn btn-success', 'target' => '_blank']);
                        }
                        ?>
                    </div>
                    <div class="col-4 d-flex justify-content-center">
                        <?php
                        if($test->status == 'public'){
                            echo Html::a(Yii::t('app', 'Тест') ,
                                ['/test/view', 'id' => $test->id],
                                ['class' => 'btn btn-primary']);

                        }else if($test->status == 'finished'){
                            echo Html::a(Yii::t('app', 'Қайта жариялау') ,
                                ['test/publish', 'id' => $test->id],
                                [
                                    'class' => 'btn btn-warning',
                                    'data' => [
                                        'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                                    ]
                                ]);
                            echo '<br>';
                            echo Html::a(Yii::t('app', 'Қатысушылар') ,
                                ['/test-taker/index', 'id' => $test->id],
                                ['class' => 'btn btn-primary mt-1']);

                        }else if($test->status == 'certificated'){
                            echo Html::a(Yii::t('app', 'Қатысушылар') ,
                                ['/test-taker/index', 'id' => $test->id],
                                ['class' => 'btn btn-primary']);
                        }
                        ?>
                    </div>
                    <div class="col-4 d-flex justify-content-center">
                        <?= Html::a(Yii::t('app', 'Өшіру'),
                            ['test/delete', 'id' => $test->id],
                            [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                                    'method' => 'post',
                                ],
                            ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <?php Pjax::begin(); ?>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-hover table-bordered'],
            'rowOptions' => function($model) {
                return [
                    'onclick' => 'window.location.href="' . Url::to(['teacher/update', 'id' => $model->id]) . '";',
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
                        return Url::toRoute(['teacher/delete', 'id' => $model->id]);
                    }
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>
    </div>

</div>
