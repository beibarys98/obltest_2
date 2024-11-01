<?php

use common\models\Answer;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Test $test */
/** @var $questions*/

$title = $test->language === 'ru'
    ? $test->subject->title_ru . '_' . $test->language . '_' . $test->version
    : $test->subject->title . '_' . $test->language . '_' . $test->version;
$this->title = $title;

\yii\web\YiiAsset::register($this);

?>
<div class="test-view">

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
                            'class' => 'btn btn-success',
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
                    echo Html::a(Yii::t('app', 'Қатысушылар') ,
                        ['/test/participants', 'id' => $test->id],
                        ['class' => 'btn btn-primary w-100']);
                }else if($test->status == 'finished'){
                    echo Html::a(Yii::t('app', 'Нәтиже') ,
                        ['test/result', 'id' => $test->id],
                        [
                            'class' => 'btn btn-info w-100 mb-1',
                        ]);
                    echo '<br>';
                    echo Html::a(Yii::t('app', 'Қатысушылар') ,
                        ['/test/participants', 'id' => $test->id],
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
                    echo Html::a(Yii::t('app', 'Қатысушылар') ,
                        ['/test/participants', 'id' => $test->id],
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

    <div style="font-size: 24px;">
        <?php $number = 1; ?>
        <?php foreach ($questions as $q): ?>
            <?= Html::a('+', ['add-formula', 'id' => $q->id, 'type' => 'question'], [
                'class' => 'btn btn-primary',
            ]) ?>
            <?= $number++ . '. '; ?>
            <?php if ($q->formula_path): ?>
                <!-- Display the formula image if it exists -->
                <?= Html::img(Url::to('@web/' . $q->formula_path)) ?>
            <?php else: ?>
                <!-- Display the question text if no formula exists -->
                <?= $q->content; ?>
            <?php endif; ?>
            <br>
            <?php
            $answers = Answer::find()
                ->andWhere(['question_id' => $q->id])
                ->all();
            $alphabet = range('A', 'Z');
            $index = 0;
            ?>
            <?php foreach ($answers as $a): ?>
                <?= Html::a('+', ['add-formula', 'id' => $a->id, 'type' => 'answer'], [
                    'class' => 'btn btn-secondary',
                ]) ?>
                <?php if ($a->formula_path): ?>
                    <?php if ($a->id == $q->answer_id): ?>
                        <strong><?= $alphabet[$index++] . '. '?></strong>
                        <?= Html::img(Url::to('@web/' . $a->formula_path)) ?>
                        <br>
                    <?php else: ?>
                        <?= $alphabet[$index++] . '. ' ?>
                        <?= Html::img(Url::to('@web/' . $a->formula_path)) ?>
                        <br>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($a->id == $q->answer_id): ?>
                        <strong><?= $alphabet[$index++] . '. ' . $a->content; ?></strong><br>
                    <?php else: ?>
                        <?= $alphabet[$index++] . '. ' . $a->content; ?><br>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <br>
        <?php endforeach; ?>
    </div>
</div>
