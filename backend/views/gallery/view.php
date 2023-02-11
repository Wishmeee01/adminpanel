<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Gallery $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Galleries', 'url' => ['index']];
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
                                    'id',
                                    [
                                        'attribute' => 'category_id',
                                        'value' => function ($model) {
                                            return \backend\models\GalleryCategory::getname($model->category_id);
                                        },
                                    ],
                                    [
                                        'attribute' => 'image_link',
                                        'format'=>'raw',
                                        'value' => function ($model) {
                                            return '<img src="'.$model->image_link.'" height="200px" width="200px">';
                                        },
                                    ],
                                    'image_link',
                                    [
                                        'attribute' => 'uploaded_at',
                                        'value' => function ($model) {
                                            return date('d-M-Y h:i:s',$model->uploaded_at);
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
