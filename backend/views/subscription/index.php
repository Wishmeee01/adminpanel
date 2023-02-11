<?php

use backend\models\Subscription;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\SubscriptionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Subscriptions';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">

                <?php
                $columns = [
                    ['class' => 'kartik\grid\SerialColumn'],
                    'plan_name',
                    'validity_in_days',
                    'amount',
                    'currency',
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            return $model->status == 1 ? 'Active' : 'Inactive';
                        },
                        'filter' => array("1" => "Active", "0" => "Inactive"),
                    ],
                    //'created_at',
                    //'updated_at',
                    //'verification_token',
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'header' => 'Actions',
                        'headerOptions' => [
                            'style' => 'color:#337ab7'
                        ],
                        'template' => '{view}{update}{delete}'
                    ]
                        ]
                ?>
                <?=
                DynaGrid::widget([
                    'columns' => $columns,
                    'theme' => 'panel-success',
                    'showPersonalize' => true,
                    //'storage' => 'session',
                    'gridOptions' => [
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'showPageSummary' => true,
                        'floatHeader' => true,
                        'pjax' => true,
                        'responsiveWrap' => false,
                        'panel' => [
                            'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-user"></i>  ' . $this->title . '</h3>',
                            'before' => '<div style="padding-top: 7px;"><em>&nbsp;</em></div>',
                            'after' => false
                        ],
                        'toolbar' => [
                            ['content' =>
                                Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'], ['data-pjax' => 0, 'class' => 'btn btn-success', 'title' => 'Add', 'id' => 'Add'])
                            ],
                            '{export}',
                        ],
                        'export' => [
                            'id' => 'sites-all-export',
                            'fontAwesome' => true,
                            'showConfirmAlert' => false,
                            'target' => GridView::TARGET_BLANK
                        ],
                    ],
                    'options' => ['id' => $this->title] // a unique identifier is important
                ]);
                ?>


            </div>
        </div>
    </div>
</div>
