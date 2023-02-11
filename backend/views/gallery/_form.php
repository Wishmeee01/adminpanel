<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
/** @var yii\web\View $this */
/** @var backend\models\Gallery $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="gallery-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'category_id')->dropDownList(ArrayHelper::map(\backend\models\GalleryCategory::find()->asArray()->all(), 'id', 'name'), [
                        'prompt' => 'Select Option'
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-line">
                    <?= $form->field($model, 'image_link')->fileInput(['class'=>'form-control'])->label('Image Upload'); ?>
                </div>
            </div>
        </div>
       
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
