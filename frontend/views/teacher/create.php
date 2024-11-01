<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var $user */
/** @var $teacher */

use common\models\Subject;
use kartik\select2\Select2;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

$this->title = 'Жаңа мұғалім';
?>
<div class="site-signup">

    <h1 class="text-center"><?= $this->title ?></h1>

    <div>
        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

        <?= $form->field($user, 'username')->textInput(['autofocus' => true, 'placeholder' => 'ИИН'])->label(false) ?>

        <?= $form->field($teacher, 'name')->textInput(['placeholder' => 'Есімі'])->label(false) ?>

        <?= $form->field($teacher, 'school')->textInput(['placeholder' => 'Мекеме'])->label(false) ?>

        <?php
        $subjectField = (Yii::$app->language === 'ru-RU') ? 'title_ru' : 'title';
        $subjects = ArrayHelper::map(Subject::find()->all(), 'id', $subjectField);
        ?>

        <?=
        $form->field($teacher, 'subject_id')
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
        $form->field($teacher, 'language')
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

        <?= $form->field($user, 'password')->passwordInput(['placeholder' => 'Құпия сөз'])->label(false) ?>

        <div class="form-group">
            <?= Html::submitButton('Сақтау', ['class' => 'btn btn-success w-100', 'name' => 'signup-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
