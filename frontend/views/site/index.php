<?php

use common\models\Question;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Test $test */
/** @var $certificate*/
/** @var $isActive*/
/** @var $hasPaid*/

$title = $test->language === 'ru'
    ? $test->subject->title_ru
    : $test->subject->title;
$this->title = $title;

\yii\web\YiiAsset::register($this);

?>
<div class="test-view">

    <div class="mt-1" style="margin: 0 auto; width: 500px;">

        <div class="btn disabled w-100" style="font-size: 24px;">
            <?= $title ?>
        </div>

        <div class="mt-5">
            <?= GridView::widget([
                'dataProvider' => $certificate,
                'layout' => "{items}",
                'tableOptions' => ['class' => 'table table-bordered shadow-sm', 'style' => 'border-radius: 10px; overflow: hidden'],
                'columns' => [
                    [
                        'attribute' => 'path',
                        'label' => Yii::t('app', 'Сертификат'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a('Сертификат',
                                ['download', 'id' => $model->id]);
                        },
                    ],
                ],
            ]); ?>
        </div>

        <div class="mt-5 text-center">
            <?php
            $isActive ? $class = 'active' : $class = 'disabled';
            if(!$hasPaid){
                echo Html::a(
                    Yii::t('app', 'Оплатить'),
                    ['site/pay', 'id' => $test->id],
                    ['class' => 'btn btn-primary w-100 '.$class]);
            }else{
                $firstQuestion = Question::find()
                    ->where(['test_id' => $test->id])
                    ->orderBy(['id' => SORT_ASC])
                    ->one();

                $firstQuestionId = $firstQuestion ? $firstQuestion->id : null;

                echo Html::a(
                    Yii::t('app', 'Начать'),
                    ['view', 'id' => $firstQuestionId], // Pass the first question's ID
                    [
                        'class' => 'btn btn-success w-100 ' . $class,
                        'data' => [
                            'confirm' => Yii::t('app', 'Вы уверены?'),
                            'method' => 'post',
                        ],
                    ]
                );
            }
            ?>
        </div>

        <div class="mt-5" style="width: 500px; margin: 0 auto;">
            <div class="accordion shadow-sm" style="font-size: 24px;">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            <?= Yii::t('app', 'Инструкция') ?>
                        </button>
                    </h2>
                    <div class="accordion-collapse collapse" id="collapseOne">
                        <div class="accordion-body" style="font-size: 16px;">
                            <!-- YouTube video embed -->
                            <iframe width="100%" height="270px" src="<?= Yii::t('app', 'https://www.youtube.com/embed/ZYeX8mDePPI') ?>"
                                    title="YouTube video player" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                            </iframe>
                            <br>
                            <br>
                            1.	<?= Yii::t('app', 'Вопросы олимпиады: По предмету – 50 вопросов;') ?> <br>
                            2.	<?= Yii::t('app', 'Время тестирования – 60 минут. По предметам математика, физика, химия – 120 минут. (по истечении времени тестирование автоматически закрывается);') ?> <br>
                            3.	<?= Yii::t('app', 'Из предложенных 4 ответов нужно выбрать 1 правильный ответ;') ?> <br>
                            4.	<?= Yii::t('app', '1 правильный ответ – 1 балл;') ?> <br>
                            5.	<?= Yii::t('app', 'Участник должен указать полные сведения о себе (Ф.И.О. по удостоверению личности, указать название города, района, наименование школы);') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


</div>
