<?php
/** @var \app\models\entities\CustomerRequestEntity $model */
$productTitle = $model->product ? $model->product->title : '—';
$phone = \Yii::$app->params['supportPhone'] ?? '+7 (800) 000-00-00';
?>
Здравствуйте!

Ваша заявка №<?= $model->id ?> принята в работу.
Наш менеджер свяжется с вами в ближайшее время.

Номер заявки: <?= $model->id ?>
<?php if ($model->product): ?>
Товар: <?= $productTitle ?>
<?php endif; ?>
Статус: Принята в работу
<?php if (!empty($model->wishlist)): ?>

Ваши пожелания:
<?= $model->wishlist ?>
<?php endif; ?>

По всем вопросам: <?= $phone ?>

--
СПЕЦАВТОСИЛА