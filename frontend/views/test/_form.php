<?php

use common\models\Subject;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Test $model */
/** @var yii\bootstrap5\ActiveForm $form */
?>

<div class="test-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($model, 'file')
        ->input('file', ['class' => 'form-control'])
        ->label(false) ?>

    <?php
    $subjectField = (Yii::$app->language === 'ru-RU') ? 'title_ru' : 'title';
    $subjects = ArrayHelper::map(Subject::find()->all(), 'id', $subjectField);
    ?>

    <?=
    $form->field($model, 'subject_id')
        ->widget(Select2::classname(),
            [
                'data' => $subjects,
                'options' => [
                    'placeholder' => 'Пән',
                    'style' => ['width' => '100%'],
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownAutoWidth' => true,
                    'maximumInputLength' => 20,
                ],
            ])->label(false);
    ?>

    <?php
    $languages = [
        'kz' => Yii::t('app', 'казахский'),
        'ru' => Yii::t('app', 'русский'),
    ];
    ?>

    <?=
    $form->field($model, 'language')
        ->widget(Select2::classname(),
            [
                'data' => $languages,
                'options' => [
                    'placeholder' => 'Тест тапсыру тілі',
                    'style' => ['width' => '100%'],
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownAutoWidth' => true,
                    'maximumInputLength' => 20,
                ],
            ])->label(false);
    ?>

    <?= $form->field($model, 'version')->textInput(['placeholder' => 'Нұсқа'])->label(false) ?>

    <?= $form->field($model, 'duration')->input('time')->label(false);?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сақтау'), ['class' => 'btn btn-success w-100']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
