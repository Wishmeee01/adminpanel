<?php


 \common\components\ExcelGrid::widget([ 
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 //'extension'=>'xlsx',
 //'filename'=>'excel',
 'properties' =>[
 //'creator' =>'',
 //'title'  => '',
 //'subject'  => '',
 //'category' => '',
 //'keywords'  => '',
 //'manager'  => '',
 ],
        'columns' => [
             ['class' => 'yii\grid\SerialColumn'],
             'product_name',
              'mrp',
              'selling_price',
        ],
    ]);
?>