<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class DashboardAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'vendor/fonts/boxicons.css',
        'vendor/css/core.css',
        'vendor/css/theme-default.css',
        'css/demo.css',
        'vendor/libs/perfect-scrollbar/perfect-scrollbar.css',
        'vendor/libs/apex-charts/apex-charts.css',
    ];
    public $js = [
        'vendor/js/helpers.js',
        'js/config.js',
        //'vendor/libs/jquery/jquery.js',
        'vendor/libs/popper/popper.js',
        'vendor/js/bootstrap.js',
        'vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
        'vendor/js/menu.js',
        'vendor/libs/apex-charts/apexcharts.js',
        'js/main.js',
        'js/dashboards-analytics.js',
        'js/clone.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
