<?php

use kartik\editors\Summernote;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Subscription $model */
/** @var yii\widgets\ActiveForm $form */
if($model->offer_status ==1)
{
    $show = "block";
} else {
    $show = "none";
}
?>

<div class="subscription-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row clearfix">
        <div class="col-md-4">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'plan_name')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'validity_in_days')->textInput() ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'cycle')->textInput() ?>
                </div>
            </div>
        </div>

    </div>

    <div class="row clearfix">
        <div class="col-md-4">
            <div class="form-group">
                <div class="form-line">
                    <?=
                    $form->field($model, 'currency')->dropDownList(['USD' => 'USD'], [
                        'prompt' => 'Select Option'
                    ])
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'amount')->textInput() ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <div class="form-line">
                    <?=
                    $form->field($model, 'offer_status')->dropDownList(['1' => 'Yes', '0' => 'No'], [
                        'prompt' => 'Select Option'
                    ])
                    ?>
                </div>
            </div>
        </div>

    </div>
    <div class="row clearfix">
        <div class="col-md-12">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'icon')->fileInput(['class' => 'form-control'])->label('Icon Upload'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'feature1')->textArea() ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'feature2')->textArea() ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row clearfix">
        <div class="col-md-12">
            <div class="form-group">
                <div class="form-line">
                    <?=
                    $form->field($model, 'description')->textArea();
                    ?>
                </div>
            </div>
        </div>
    </div>
  
    <div class="offerscl" style="display:<?=$show ?>">
    <h5>Add Offers</h5>
    
    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($offers, 'offer_name')->textInput() ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($offers, 'offer_price')->textInput() ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($offers, 'offer_start_date')->textInput(['type'=>'date']) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($offers, 'offer_end_date')->textInput(['type'=>'date']) ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
