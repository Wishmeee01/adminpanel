<?php
/** @var \yii\web\View $this */

/** @var string $content */
use backend\assets\DashboardAsset;
use common\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use backend\models\Pages;

DashboardAsset::register($this);
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
    <body>
        <?php $this->beginBody() ?>
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                    <div class="app-brand demo">
                        <a href="<?php echo Yii::getAlias('@web') ?>" class="app-brand-link">
                            <span class="app-brand-logo demo">
<!--                                <img width="80%" src="<?= Yii::$app->request->baseUrl ?>/img/logo/logo.png" >-->
                            </span>
                        </a>

                        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                            <i class="bx bx-chevron-left bx-sm align-middle"></i>
                        </a>
                    </div>

                    <div class="menu-inner-shadow"></div>

                    <ul class="menu-inner py-1">
                        <!-- Dashboard -->
                        <li class="menu-item active">
                            <a href="<?php echo Yii::getAlias('@web') ?>" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                                <div data-i18n="Analytics">Dashboard</div>
                            </a>
                        </li>

                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">Pages</span>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo Yii::getAlias('@web') ?>/user" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                                <div data-i18n="Basic">Users</div>
                            </a>
                        </li>
                        
                        <li class="menu-item">
                            <a href="<?php echo Yii::getAlias('@web') ?>/media" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-video-plus"></i>
                                <div data-i18n="Basic">Media</div>
                            </a>
                        </li>
                        
                        <li class="menu-item">
                            <a href="<?php echo Yii::getAlias('@web') ?>/gallery-category" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-category"></i>
                                <div data-i18n="Basic">Gallery Category</div>
                            </a>
                        </li>
                        
                        <li class="menu-item">
                            <a href="<?php echo Yii::getAlias('@web') ?>/gallery" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-images"></i>
                                <div data-i18n="Basic">Gallery</div>
                            </a>
                        </li>
                        
                        <li class="menu-item">
                            <a href="<?php echo Yii::getAlias('@web') ?>/subscription" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-badge-check"></i>
                                <div data-i18n="Basic">Subscriptions</div>
                            </a>
                        </li>
                    </ul>
                </aside>
                <div class="layout-page">
                    <!-- Navbar -->

                    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                         id="layout-navbar">
                        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                                <i class="bx bx-menu bx-sm"></i>
                            </a>
                        </div>

                        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

                            <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <!-- Place this tag where you want the button to render. -->
                                <!-- User -->
                                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                        <div class="avatar avatar-online">
                                            <img src="<?= Yii::$app->request->baseUrl ?>/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar avatar-online">
                                                            <img src="<?= Yii::$app->request->baseUrl ?>/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <span class="fw-semibold d-block">Wishme</span>
                                                        <small class="text-muted">Admin</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <!--                    <li>
                                                              <div class="dropdown-divider"></div>
                                                            </li>
                                                            
                                                            <li>
                                                              <a class="dropdown-item" href="#">
                                                                <i class="bx bx-cog me-2"></i>
                                                                <span class="align-middle">Settings</span>
                                                              </a>
                                                            </li>-->

                                        <li>
                                            <div class="dropdown-divider"></div>
                                        </li>
                                        <li>
                                            <?= Html::a('<i class="bx bx-power-off me-2"></i><span class="align-middle">Log Out</span>', ['/site/logout'], ['data-method' => 'post', 'class' => 'dropdown-item']) ?>
                                        </li>
                                    </ul>
                                </li>
                                <!--/ User -->
                            </ul>
                        </div>
                    </nav>
                    <div class="content-wrapper">
                        <?= $content ?>
                        <footer class="content-footer footer bg-footer-theme">
                            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                                <div class="mb-2 mb-md-0">
                                    ©
                                    <script>
                                        document.write(new Date().getFullYear());
                                    </script>
                                    , made with ❤️ by Wishme

                                </div>

                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </div>


        <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage();
