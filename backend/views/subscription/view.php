<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Subscription $model */
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-12">
                        <div class="card-body">
                            <p>
                                <?= Html::a('Add', ['create'], ['class' => 'btn btn-success']) ?>
                                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                <?=
                                Html::a('Delete', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this item?',
                                        'method' => 'post',
                                    ],
                                ])
                                ?>
                            </p>

                            <?=
                            DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'plan_name',
                                    'validity_in_days',
                                    'amount',
                                    'currency',
                                    'description:ntext',
                                    'cycle',
                                    [
                                        'attribute' => 'icon',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            return '<img src="' . $model->icon . '" height="200px" width="200px">';
                                        },
                                    ],
                                    'icon',
                                    [
                                        'attribute' => 'offer_status',
                                        'value' => function ($model) {
                                            return $model->offer_status == 1 ? 'Yes' : 'No';
                                        },
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => function ($model) {
                                            return $model->status == 1 ? 'Active' : 'Inactive';
                                        },
                                    ],
                                ],
                            ])
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($model->offer_status == 1) { ?>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-12">
                            <div class="card-body">
                                <h5>Offer Details</h5>
                                <?=
                                DetailView::widget([
                                    'model' => $offers,
                                    'attributes' => [
                                        'offer_name',
                                        'offer_price',
                                        [
                                            'attribute' => 'offer_start_date',
                                            'value' => function ($model) {
                                                return date('d M, Y', strtotime($model->offer_start_date));
                                            },
                                        ],
                                        [
                                            'attribute' => 'offer_end_date',
                                            'value' => function ($model) {
                                                return date('d M, Y', strtotime($model->offer_end_date));
                                            },
                                        ],
                                        
                                        [
                                            'attribute' => 'createdAt',
                                            'value' => function ($model) {
                                                return date('d M, Y h:i:s', strtotime($model->createdAt));
                                            },
                                        ],
                                        [
                                            'attribute' => 'updatedAt',
                                            'value' => function ($model) {
                                                return date('d M, Y h:i:s', strtotime($model->updatedAt));
                                            },
                                        ],
                                        [
                                            'attribute' => 'status',
                                            'value' => function ($model) {
                                                return $model->status == 1 ? 'Active' : 'Inactive';
                                            },
                                        ],
                                    ],
                                ])
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
