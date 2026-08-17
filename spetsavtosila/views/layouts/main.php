<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\models\Parameter;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? 'СПЕЦАВТОСИЛА - комплектующие для топливозаправщиков и бензовозов']);
$this->registerMetaTag(['name' => 'keywords', 'content' => 'топливозаправщики, бензовозы, комплектующие, запчасти']);

$siteName = Parameter::getValue('site_name', 'СПЕЦАВТОСИЛА');
$sitePhone = Parameter::getValue('site_phone', '+7 (XXX) XXX-XX-XX');
$siteEmail = Parameter::getValue('site_email', 'info@spetsavtosila.ru');

$this->title = $siteName;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <!-- Top bar with contact info -->
    <div class="bg-dark text-light py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 d-none d-md-block">
                    <a href="/" class="text-light text-decoration-none fw-bold">
                        <?= Html::encode($siteName) ?>
                    </a>
                </div>
                <div class="col-md-4 text-center">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sitePhone) ?>" class="text-light text-decoration-none">
                        <i class="bi bi-telephone"></i> <?= Html::encode($sitePhone) ?>
                    </a>
                </div>
                <div class="col-md-4 text-end">
                    <a href="mailto:<?= $siteEmail ?>" class="text-light text-decoration-none">
                        <i class="bi bi-envelope"></i> <?= Html::encode($siteEmail) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand d-md-none" href="/">
                <?= Html::encode($siteName) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?= Nav::widget([
                    'options' => ['class' => 'navbar-nav ms-auto'],
                    'items' => [
                        ['label' => 'Каталог', 'url' => ['/category/index']],
                        ['label' => 'О компании', 'url' => ['/site/about']],
                        ['label' => 'Контакты', 'url' => ['/site/contact']],
                        ['label' => 'Информация', 'url' => ['/site/info']],
                    ],
                ]) ?>
            </div>
        </div>
    </nav>
</header>

<main class="flex-shrink-0">
    <div class="container mt-4">
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-4 bg-dark text-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><?= Html::encode($siteName) ?></h5>
                <p><?= Parameter::getValue('site_address', 'г. Москва, ул. Примерная, д. 1') ?></p>
            </div>
            <div class="col-md-4">
                <h5>Навигация</h5>
                <ul class="list-unstyled">
                    <li><a href="/catalog" class="text-light text-decoration-none">Каталог</a></li>
                    <li><a href="/site/about" class="text-light text-decoration-none">О компании</a></li>
                    <li><a href="/site/contact" class="text-light text-decoration-none">Контакты</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Контакты</h5>
                <p>
                    <i class="bi bi-telephone"></i> <?= Html::encode($sitePhone) ?><br>
                    <i class="bi bi-envelope"></i> <?= Html::encode($siteEmail) ?>
                </p>
                <h6>Социальные сети</h6>
                <div>
                    <?php $vk = Parameter::getValue('social_vk'); ?>
                    <?php if ($vk): ?>
                        <a href="<?= Html::encode($vk) ?>" class="text-light me-2" target="_blank">
                            <i class="bi bi-vk"></i> ВКонтакте
                        </a>
                    <?php endif; ?>
                    <?php $tg = Parameter::getValue('social_telegram'); ?>
                    <?php if ($tg): ?>
                        <a href="<?= Html::encode($tg) ?>" class="text-light" target="_blank">
                            <i class="bi bi-telegram"></i> Telegram
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <hr class="my-3">
        <div class="text-center">
            <small>&copy; <?= date('Y') ?> <?= Html::encode($siteName) ?>. Все права защищены.</small>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
