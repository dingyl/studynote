<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id'=>"add-form",
    'options'=>['class'=>'form-horizontal']
])
?>

<?= $form->field($model,'username');?>
<?= $form->field($model,'password');?>
<?= $form->field($model,'repassword');?>

<div class="form-group">
    <?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end() ?>

