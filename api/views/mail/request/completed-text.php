<?php
/** @var \app\models\entities\CustomerRequestEntity $model */
$productTitle = $model->product ? $model->product->title : '—';
$phone = \Yii::$app->params['supportPhone'] ?? '+7 (800) 000-00-00';
?>
Здравствуйте!

Ваш заказ №<?= $model->id ?> готов!

Номер заявки: <?= $model->id ?>
<?php if ($model->product): ?>
Товар: <?= $productTitle ?>
<?php endif; ?>
Статус: Готов к выдаче

Наш менеджер свяжется с вами для согласования деталей получения.

По всем вопросам: <?= $phone ?>

--
СПЕЦАВТОСИЛА
Спасибо, что выбрали нас!