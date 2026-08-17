<?php

/** @var yii\web\View $this */
/** @var app\models\Category[] $dataProvider */

use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Каталог товаров';
?>

<h1 class="mb-4">Каталог товаров</h1>

<div class="row g-4">
    <?php foreach ($dataProvider->models as $category): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card category-card h-100">
            <?php if ($category->image): ?>
                <img src="<?= Html::encode($category->image) ?>" class="card-img-top" alt="<?= Html::encode($category->title) ?>">
            <?php else: ?>
                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 250px;">
                    <i class="bi bi-box-seam" style="font-size: 4rem;"></i>
                </div>
            <?php endif; ?>
            <div class="category-card-overlay">
                <h3 class="h4"><?= Html::encode($category->title) ?></h3>
                <?php if ($category->description): ?>
                    <p class="small mb-2"><?= Html::encode(mb_substr($category->description, 0, 100)) ?>...</p>
                <?php endif; ?>
                <a href="/category/<?= $category->id ?>" class="btn btn-primary">Перейти в каталог</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($dataProvider->totalCount > 12): ?>
<div class="mt-4">
    <?= \yii\widgets\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'options' => ['class' => 'pagination justify-content-center'],
    ]) ?>
</div>
<?php endif; ?>
