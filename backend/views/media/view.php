<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Media */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Media', 'url' => ['index']];
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
                                    [
                                        'attribute' => 'user_id',
                                        'value' => function ($model) {
                                            return backend\models\UserDetails::getname($model->user_id);
                                        },
                                    ],
                                    [
                                        'attribute' => 'friend_id',
                                        'value' => function ($model) {
                                            return backend\models\UserDetails::getname($model->friend_id);
                                        },
                                    ],
                                    'title',
                                    [
                                        'attribute' => 'link',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            return wordwrap($model->link,120,"<br>\n",true);
                                            //return Html::a($model->link, $model->link, ['target' => '_blank', 'data-pjax' => "0"]);
                                        },
                                    ],
                                    'tags',
                                    'description:ntext',
                                    'month',
                                    'year',
                                    [
                                        'attribute' => 'created',
                                        'value' => function ($model) {
                                            return date('d M, Y', $model->created);
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