<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */

/** @var \common\models\LoginForm $model */
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Login';
?>
<h4 class="mb-2 text-center">Welcome to Wish Me! 👋</h4>
<p class="mb-4 text-center">Please sign-in to your account and start the adventure</p>

<?php $form = ActiveForm::begin(['id' => 'formAuthentication', 'class' => 'mb-3']); ?>
<div class="mb-3">
    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
</div><div class="mb-3">
    <?= $form->field($model, 'password')->passwordInput() ?>
</div><div class="mb-3">
    <?= $form->field($model, 'rememberMe')->checkbox() ?>
</div>
<div class="form-group">
    <?= Html::submitButton('Login', ['class' => 'btn btn-primary d-grid w-100', 'name' => 'login-button']) ?>
</div>

<?php ActiveForm::end(); ?>
    
