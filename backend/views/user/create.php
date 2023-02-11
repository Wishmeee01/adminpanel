<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-12">
                        <div class="card-body">

                            <h5><?= Html::encode($this->title) ?></h5>

                            <?=
                            $this->render('_form', [
                                'model' => $model,
                                'details' => $details,
                            ])
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
