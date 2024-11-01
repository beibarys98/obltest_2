<?php

use common\models\Test;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var common\models\TestSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var $percentage */
/** @var $purpose */

$this->title = Yii::t('app', 'Баптаулар');
?>
<div class="test-index">

    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <div class="p-3 shadow-sm mb-3" style="border: 1px solid black; border-radius: 10px; margin: 0 auto; width: 500px;">
        <?php $form = ActiveForm::begin(); ?>

        <div class="d-flex">
            <div style="width: 20%" class="p-1">
                <?= $form->field($percentage, 'first')->textInput()->label('Бірінші') ?>
            </div>
            <div style="width: 20%" class="p-1">
                <?= $form->field($percentage, 'second')->textInput()->label('Екінші') ?>
            </div>
            <div style="width: 20%" class="p-1">
                <?= $form->field($percentage, 'third')->textInput()->label('Үшінші') ?>
            </div>
            <div style="width: 20%" class="p-1">
                <?= $form->field($percentage, 'fourth')->textInput()->label('Алғыс хат') ?>
            </div>
            <div style="width: 20%" class="p-1">
                <?= $form->field($percentage, 'fifth')->textInput()->label('Сертификат') ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Сақтау', ['class' => 'btn btn-primary w-100',
                'style' => 'text-align: center;']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="p-3 shadow-sm" style="border: 1px solid black; border-radius: 10px; margin: 0 auto; width: 400px;">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($purpose, 'purpose')->textInput()->label('Назначение платежа') ?>

        <?= $form->field($purpose, 'cost')->textInput()->label('Сумма') ?>

        <div class="form-group">
            <?= \yii\helpers\Html::submitButton('Сақтау',
                ['class' => 'btn btn-primary w-100', 'style' => 'text-align: center;']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
