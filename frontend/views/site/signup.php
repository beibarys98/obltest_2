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

$this->title = 'Signup';
?>
<div class="site-signup mt-1">
    <div style="margin: 0 auto; width: 500px;">
        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($user, 'username')->textInput(['autofocus' => true, 'placeholder' => 'ИИН'])->label(false) ?>

            <?= $form->field($teacher, 'name')->textInput(['placeholder' => 'Имя'])->label(false) ?>

            <?= $form->field($teacher, 'school')->textInput(['placeholder' => 'Организация'])->label(false) ?>

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
                            'placeholder' => 'Предмет',
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
                            'placeholder' => 'Язык сдачи теста',
                            'style' => ['width' => '100%'],
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownAutoWidth' => true,
                            'maximumInputLength' => 20,
                        ],
                    ])->label(false);
            ?>

            <?= $form->field($user, 'password')->passwordInput(['placeholder' => 'Пароль'])->label(false) ?>

            <div class="form-group text-center">
                <?= Html::submitButton('Регистрация', ['class' => 'btn btn-success', 'name' => 'signup-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>

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
