<?php
/** @var \app\entities\CustomerRequestEntity $model */
$productTitle = $model->product ? $model->product->title : '—';
$phone = \Yii::$app->params['supportPhone'] ?? '+7 (800) 000-00-00';
?>
Здравствуйте!

К сожалению, ваша заявка №<?= $model->id ?> была отменена.

Номер заявки: <?= $model->id ?>
<?php if ($model->product): ?>
Товар: <?= $productTitle ?>
<?php endif; ?>
Статус: Отменена
<?php if (!empty($model->admin_notes)): ?>

Причина:
<?= $model->admin_notes ?>
<?php endif; ?>

Если у вас остались вопросы:
<?= $phone ?>

--
СПЕЦАВТОСИЛА