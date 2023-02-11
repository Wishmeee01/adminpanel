<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = \backend\models\UserDetails::getname($model->id);
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
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

                            <h5><?= Html::encode($this->title) ?></h5>

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
                                    'email:email',
                                    [
                                        'attribute' => 'mobile',
                                        'value' => function ($model) {
                                            return $model->country_code . ' ' . $model->mobile;
                                        },
                                    ],
                                    'username',
                                    'device_id',
                                    [
                                        'attribute' => 'social_login',
                                        'value' => function ($model) {
                                            return $model->social_login == 0 ? 'Direct' : 'Facebook';
                                        },
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => function ($model) {
                                            return $model->status == 10 ? 'Active' : 'Inactive';
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
