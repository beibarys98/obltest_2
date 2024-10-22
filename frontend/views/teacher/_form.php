<?php

use common\models\Subject;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Teacher $model */
/** @var yii\bootstrap5\ActiveForm $form */

?>

<div class="teacher-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Есім'])->label(false) ?>

    <?= $form->field($model, 'school')->textInput(['maxlength' => true, 'placeholder' => 'Мекеме'])->label(false) ?>

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

    <?= $form->field($model, 'start_time')->textInput(['placeholder' => 'Бастады'])->label(false) ?>

    <?= $form->field($model, 'end_time')->textInput(['placeholder' => 'Аяқтады'])->label(false) ?>

    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Жаңа құпия сөз'])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сақтау'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
