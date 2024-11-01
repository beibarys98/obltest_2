<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Subject $model */

$this->title = Yii::t('app', 'Жаңа пән');
?>
<div class="subject-create">

    <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
