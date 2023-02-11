<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\Media */
/* @var $form yii\widgets\ActiveForm */
$month = [];
for($i=1;$i<13;$i++)
{
$month[$i] = $i;
}
?>

<div class="media-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?=
                    $form->field($model, 'user_id')->dropDownList(ArrayHelper::map(\backend\models\UserDetails::find()->asArray()->all(), 'user_id', 'name'), [
                        'prompt' => 'Select Option'
                    ])
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?=
                    $form->field($model, 'friend_id')->dropDownList(ArrayHelper::map(\backend\models\UserDetails::find()->asArray()->all(), 'user_id', 'name'), [
                        'prompt' => 'Select Option'
                    ])
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                     <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'tags')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
         <div class="col-md-12">
            <div class="form-group">
                <div class="form-line">
                   <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
        
        
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'link')->fileInput(['class'=>'form-control']) ?>
                </div>
            </div>
        </div>
    </div>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
