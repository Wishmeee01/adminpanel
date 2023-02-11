<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\GalleryCategory $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Gallery Categories', 'url' => ['index']];
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
                                    'id',
                                    [
                                        'attribute' => 'parent_id',
                                        'value' => function ($model) {
                                            return $model->parent_id == 0 ? 'Main' : \backend\models\GalleryCategory::getname($model->parent_id);
                                        },
                                    ],
                                    'name',
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

