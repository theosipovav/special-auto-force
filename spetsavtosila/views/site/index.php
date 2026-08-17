<?php

/** @var yii\web\View $this */
/** @var app\models\Product[] $latestProducts */
/** @var app\models\Product[] $popularProducts */

use yii\helpers\Html;
use yii\bootstrap5\Carousel;
use app\models\Parameter;

$this->title = Parameter::getValue('site_name', 'СПЕЦАВТОСИЛА');
$this->params['meta_description'] = 'Отечественные и импортные комплектующие для топливозаправщиков и бензовозов';
?>

<!-- Hero Section -->
<section class="hero-section mb-5">
    <div class="container">
        <h1><?= Html::encode($this->title) ?></h1>
        <p class="lead">Отечественные и импортные комплектующие для топливозаправщиков и бензовозов</p>
        <a href="/catalog" class="btn btn-primary btn-lg mt-3">Перейти в каталог</a>
    </div>
</section>

<!-- Search Section -->
<section class="mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?= Html::beginForm(['/site/search'], 'get', ['class' => 'search-form d-flex']) ?>
                    <input type="text" name="q" class="form-control form-control-lg" placeholder="Поиск товаров..." value="<?= Html::encode(Yii::$app->request->get('q', '')) ?>">
                    <button type="submit" class="btn btn-primary btn-lg">Найти</button>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</section>

<!-- Latest Products Carousel -->
<?php if (!empty($latestProducts)): ?>
<section class="mb-5">
    <div class="container">
        <h2 class="mb-4">Последние добавленные товары</h2>
        
        <?= Carousel::widget([
            'items' => array_map(function($product) {
                $image = $product->mainImage ? $product->mainImage->image : '/images/no-image.png';
                return [
                    'content' => '<div class="card product-card border-0">' .
                        '<img src="' . Html::encode($image) . '" class="d-block w-100" alt="' . Html::encode($product->title) . '">' .
                        '<div class="card-body">' .
                            '<h5 class="card-title">' . Html::encode($product->title) . '</h5>' .
                            '<p class="card-text text-muted">' . Html::encode(mb_substr($product->short_description, 0, 100)) . '...</p>' .
                            '<a href="/product/' . $product->id . '" class="btn btn-outline-primary">Подробнее</a>' .
                        '</div>' .
                    '</div>',
                    'caption' => '',
                    'options' => ['class' => 'carousel-item active'],
                ];
            }, $latestProducts),
            'options' => ['class' => 'carousel slide product-carousel', 'data-bs-ride' => 'carousel'],
        ]) ?>
        
        <div class="text-center mt-4">
            <a href="/catalog" class="btn btn-primary">Перейти в каталог товаров</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Popular Products Carousel -->
<?php if (!empty($popularProducts)): ?>
<section class="mb-5">
    <div class="container">
        <h2 class="mb-4">Самые популярные товары</h2>
        
        <?= Carousel::widget([
            'items' => array_map(function($product) {
                $image = $product->mainImage ? $product->mainImage->image : '/images/no-image.png';
                return [
                    'content' => '<div class="card product-card border-0">' .
                        '<img src="' . Html::encode($image) . '" class="d-block w-100" alt="' . Html::encode($product->title) . '">' .
                        '<div class="card-body">' .
                            '<h5 class="card-title">' . Html::encode($product->title) . '</h5>' .
                            '<p class="card-text text-muted">' . Html::encode(mb_substr($product->short_description, 0, 100)) . '...</p>' .
                            '<a href="/product/' . $product->id . '" class="btn btn-outline-primary">Подробнее</a>' .
                        '</div>' .
                    '</div>',
                    'caption' => '',
                    'options' => ['class' => 'carousel-item'],
                ];
            }, $popularProducts),
            'options' => ['class' => 'carousel slide product-carousel', 'data-bs-ride' => 'carousel'],
        ]) ?>
        
        <div class="text-center mt-4">
            <a href="/catalog" class="btn btn-primary">Перейти в каталог товаров</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Advantages Section -->
<section class="mb-5">
    <div class="container">
        <h2 class="mb-4 text-center">Преимущества заказа через наш магазин</h2>
        
        <div class="row g-4">
            <!-- Buyer Benefits -->
            <div class="col-md-6">
                <h3 class="h4 mb-3">Выгоды для покупателя</h3>
                <div class="row g-3">
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Широкий ассортимент</h5>
                            <p class="text-muted small mb-0">Более 1000 наименований комплектующих</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Конкурентные цены</h5>
                            <p class="text-muted small mb-0">Прямые поставки от производителей</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Гарантия качества</h5>
                            <p class="text-muted small mb-0">Сертифицированная продукция</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Быстрая доставка</h5>
                            <p class="text-muted small mb-0">Отгрузка в день заказа</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Services -->
            <div class="col-md-6">
                <h3 class="h4 mb-3">Предоставляемые услуги</h3>
                <div class="row g-3">
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-gear-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Консультации специалистов</h5>
                            <p class="text-muted small mb-0">Помощь в подборе оборудования</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-gear-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Техническая поддержка</h5>
                            <p class="text-muted small mb-0">Консультации по эксплуатации</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-gear-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Доставка по России</h5>
                            <p class="text-muted small mb-0">Любой удобной транспортной компанией</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-start">
                        <i class="bi bi-gear-fill advantage-icon me-3"></i>
                        <div>
                            <h5 class="h6 mb-1">Индивидуальные условия</h5>
                            <p class="text-muted small mb-0">Скидки для постоянных клиентов</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
