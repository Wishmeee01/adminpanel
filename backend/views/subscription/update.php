<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Subscription $model */
$this->title = 'Update Subscription: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
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
                                'offers' => $offers,
                            ])
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
