<?php
/**
 * Шаблон письма: Заявка принята в работу
 *
 * @var \app\models\entities\CustomerRequestEntity $model
 */

$productTitle = $model->product ? $model->product->title : '—';
$phone = \Yii::$app->params['supportPhone'] ?? '+7 (800) 000-00-00';
$siteUrl = \Yii::$app->params['siteUrl'] ?? 'https://specavtosila.ru';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка принята в работу</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, Helvetica, sans-serif; color:#333;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:30px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <!-- Шапка -->
                <tr>
                    <td style="background:#1976d2; color:#fff; padding:24px 30px; text-align:center;">
                        <h1 style="margin:0; font-size:22px;">Заявка №<?= $model->id ?> принята в работу</h1>
                    </td>
                </tr>

                <!-- Содержимое -->
                <tr>
                    <td style="padding:30px;">
                        <p style="font-size:16px; line-height:1.6;">
                            Здравствуйте!
                        </p>
                        <p style="font-size:16px; line-height:1.6;">
                            Ваша заявка <strong>№<?= $model->id ?></strong> была успешно принята в работу нашим менеджером.
                            В ближайшее время мы свяжемся с вами для уточнения деталей.
                        </p>

                        <table width="100%" cellpadding="8" cellspacing="0" style="margin:20px 0; border:1px solid #e0e0e0; border-radius:6px;">
                            <tr>
                                <td style="background:#f9f9f9; font-weight:bold; width:40%;">Номер заявки</td>
                                <td>№<?= $model->id ?></td>
                            </tr>
                            <?php if ($model->product): ?>
                            <tr>
                                <td style="background:#f9f9f9; font-weight:bold;">Товар</td>
                                <td><?= htmlspecialchars($productTitle) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="background:#f9f9f9; font-weight:bold;">Статус</td>
                                <td style="color:#1976d2; font-weight:bold;">Принята в работу</td>
                            </tr>
                            <?php if (!empty($model->wishlist)): ?>
                            <tr>
                                <td style="background:#f9f9f9; font-weight:bold;">Ваши пожелания</td>
                                <td><?= nl2br(htmlspecialchars($model->wishlist)) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>

                        <p style="font-size:16px; line-height:1.6;">
                            Если у вас возникли вопросы, свяжитесь с нами:<br>
                            📞 <a href="tel:<?= preg_replace('/[^+\d]/', '', $phone) ?>" style="color:#1976d2; text-decoration:none;"><?= $phone ?></a>
                        </p>
                    </td>
                </tr>

                <!-- Подвал -->
                <tr>
                    <td style="background:#f4f6f8; padding:16px 30px; text-align:center; font-size:12px; color:#888;">
                        © <?= date('Y') ?> СПЕЦАВТОСИЛА. Все права защищены.<br>
                        <a href="<?= $siteUrl ?>" style="color:#1976d2; text-decoration:none;"><?= $siteUrl ?></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>