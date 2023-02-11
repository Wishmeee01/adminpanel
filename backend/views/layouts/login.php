<?php
/** @var yii\web\View $this */

/** @var string $content */
use backend\assets\LoginAsset;
use yii\helpers\Html;

LoginAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="d-flex flex-column h-100">
        <?php $this->beginBody() ?>

        <div class="container-xxl">
            <div class="authentication-wrapper authentication-basic container-p-y">
                <div class="authentication-inner">
                    <!-- Register -->
                    <div class="card">
                        <div class="card-body">
                            <!-- Logo -->
                            <div class="app-brand justify-content-center">
                                    <a href="<?php echo Yii::getAlias('@web') ?>" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                
                            </span>
                        </a>
                            </div>
                            <?= $content ?>
                            
                        </div>
                    </div>
                    <!-- /Register -->
                </div>
            </div>
        </div>

        <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage();
