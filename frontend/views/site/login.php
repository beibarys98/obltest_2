<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = Yii::$app->name;
?>
<div class="site-login mt-1">
    <div style="margin: 0 auto; width: 500px;">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'ИИН'])->label(false) ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Пароль'])->label(false) ?>

            <div class="form-group text-center">
                <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <div class="form-group" style="text-align: right">
                <?= Html::a('Регистрация', ['site/signup'], ['class' => 'btn btn-success', 'name' => 'signup-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
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
