<?php
/** @var yii\web\View $this */
/** @var $teacher*/
/** @var $purpose*/
/** @var $payment*/

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<div class="mt-1" style="text-align: center;">
    <img src="/images/qr.jpg" alt="" width="200px;" style="border: 1px solid black; border-radius: 10px;" class="shadow-sm p-1">
</div>

<div class="mt-1" style="width: 500px; margin: 0 auto;">
    <div class="shadow-sm p-3" style="border: 1px solid black; border-radius: 10px;" >
        <label for="name">ФИО учащегося</label>
        <br>
        <input id="name" type="text" value="<?= $teacher->name ?>" class="w-100" disabled>
        <br>
        <label for="name">Назначение платежа</label>
        <br>
        <input id="name" type="text" value="<?= $purpose->purpose ?>" class="w-100" disabled>
        <br>
        <label for="name">Сумма</label>
        <br>
        <input id="name" type="text" value="<?= $purpose->cost ?>" class="w-100" disabled>
    </div>

    <div class="mt-1">
        <?php
        $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <div class="shadow-sm p-3" style="border: 1px solid black; border-radius: 10px;" >
            <?= $form->field($payment, 'file')->fileInput()->label('Квитанция') ?>
            <?= Html::submitButton(Yii::t('app', 'Загрузить'), ['class' => 'btn btn-primary w-100']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="accordion shadow-sm mt-5" style="font-size: 24px;">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    <?= Yii::t('app', 'Инструкция') ?>
                </button>
            </h2>
            <div class="accordion-collapse collapse" id="collapseOne">
                <div class="accordion-body" style="font-size: 16px;">
                    1) <?= Yii::t('app', 'Отправьте квитанцию себе на WhatsApp') ?>. <br>
                    2) <?= Yii::t('app', 'Нажмите на эту ссылку') ?> <a href="https://web.whatsapp.com" target="_blank">web.whatsapp.com</a>. <br>
                    3) <?= Yii::t('app', 'Сохраните квитанцию в папку "Загрузки"') ?>. <br>
                    4) <?= Yii::t('app', 'Выберите файл и нажмите "Загрузить"') ?>.
                </div>
            </div>
        </div>
    </div>
</div>

