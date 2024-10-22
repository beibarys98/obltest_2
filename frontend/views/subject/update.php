<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Subject $model */

$this->title = Yii::t('app', '{name}', [
    'name' => $model->title,
]);
?>
<div class="subject-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
