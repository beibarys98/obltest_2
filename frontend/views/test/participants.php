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

$title = $test->language === 'ru'
    ? $test->subject->title_ru . '_' . $test->language . '_' . $test->version
    : $test->subject->title . '_' . $test->language . '_' . $test->version;
$this->title = $title;

?>
<div class="teacher-index">

    <h1 class="text-center"><?= $title ?></h1>

    <div style="margin: 0 auto; width: 500px;" class="p-3 mb-3">
        <div class="row">
            <div class="col-4 mt-auto">
                <?php
                if($test->status == 'new'){
                    echo Html::a(Yii::t('app', 'Дайын') ,
                        ['/test/ready', 'id' => $test->id],
                        ['class' => 'btn btn-success w-100']);
                }else if($test->status == 'ready'){
                    echo Html::a(Yii::t('app', 'Жариялау'),
                        ['test/publish', 'id' => $test->id],
                        [
                            'class' => 'btn btn-success w-100',
                            'data' => [
                                'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                            ]
                        ]);
                }else if($test->status == 'public'){
                    echo Html::a(Yii::t('app', 'Аяқтау') ,
                        ['test/end', 'id' => $test->id],
                        [
                            'class' => 'btn btn-success w-100',
                            'data' => [
                                'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                            ]
                        ]);
                }else if($test->status == 'finished'){
                    echo Html::a(Yii::t('app', 'Қайта жариялау') ,
                        ['test/publish', 'id' => $test->id],
                        [
                            'class' => 'btn btn-warning w-100',
                            'data' => [
                                'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                            ]
                        ]);
                    echo '<br>';
                    echo Html::a(Yii::t('app', 'Марапаттау') ,
                        ['test/present', 'id' => $test->id],
                        [
                            'class' => 'btn btn-success w-100 mt-1',
                            'data' => [
                                'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                            ]
                        ]);
                }else if($test->status == 'certificated'){
                    echo Html::a(Yii::t('app', 'Қайта марапаттау') ,
                        ['test/present', 'id' => $test->id],
                        [
                            'class' => 'btn btn-warning w-100',
                            'data' => [
                                'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                            ]
                        ]);
                    echo '<br>';
                }
                ?>
            </div>
            <div class="col-4 mt-auto">
                <?php
                if($test->status == 'public'){
                    echo Html::a(Yii::t('app', 'Тест') ,
                        ['/test/view', 'id' => $test->id],
                        ['class' => 'btn btn-primary w-100']);

                }else if($test->status == 'finished'){
                    echo Html::a(Yii::t('app', 'Нәтиже') ,
                        ['test/result', 'id' => $test->id],
                        [
                            'class' => 'btn btn-info w-100 mb-1',
                        ]);
                    echo '<br>';
                    echo Html::a(Yii::t('app', 'Тест') ,
                        ['/test/view', 'id' => $test->id],
                        ['class' => 'btn btn-primary w-100']);
                }else if($test->status == 'certificated'){
                    echo Html::a(Yii::t('app', 'Сертификаттар') ,
                        ['test/download-zip', 'id' => $test->id],
                        [
                            'class' => 'btn btn-info w-100 mb-1',
                        ]);
                    echo '<br>';
                    echo Html::a(Yii::t('app', 'Нәтиже') ,
                        ['test/result', 'id' => $test->id],
                        [
                            'class' => 'btn btn-info w-100 mb-1',
                        ]);
                    echo '<br>';
                    echo Html::a(Yii::t('app', 'Тест') ,
                        ['/test/view', 'id' => $test->id],
                        ['class' => 'btn btn-primary w-100']);
                }
                ?>
            </div>
            <div class="col-4 mt-auto">
                <?= Html::a(Yii::t('app', 'Өшіру'),
                    ['test/delete', 'id' => $test->id],
                    [
                        'class' => 'btn btn-danger w-100',
                        'data' => [
                            'confirm' => Yii::t('app', 'Сенімдісіз бе?'),
                            'method' => 'post',
                        ],
                    ]) ?>
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
                    'attribute' => 'start_time',
                    'label' => 'Бастады'
                ],
                [
                    'attribute' => 'end_time',
                    'label' => 'Аяқтады'
                ],
                [
                    'attribute' => 'result',
                    'label' => 'Нәтиже'
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
