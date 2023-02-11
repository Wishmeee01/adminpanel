<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                     <?= $form->field($details, 'name')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        
         <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                   <?= $form->field($details, 'date_of_birth')->textInput(['type'=>'date']) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                   <?= $form->field($details, 'anniversary_date')->textInput(['type'=>'date']) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'country_code')->textInput() ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($details, 'profile_image')->fileInput(['class'=>'form-control']) ?>
                </div>
            </div>
        </div>
    </div>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
