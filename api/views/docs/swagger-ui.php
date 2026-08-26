<?php

use yii2mod\swagger\SwaggerAsset;

/* @var $this \yii\web\View */
/** @var string $restUrl */

SwaggerAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <?php $this->head(); ?>
    <style>
        html { box-sizing: border-box; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
<?php $this->beginBody(); ?>

<div id="swagger-ui"></div>
<script>
    window.onload = function () {
        const ui = SwaggerUIBundle({
            url: "<?= $restUrl ?>",
            dom_id: '#swagger-ui',
            deepLinking: true,
            displayRequestDuration: true,
            filter: true,
            validatorUrl: null,
            // сохраняет Authorize после перезагрузки страницы
            persistAuthorization: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
        });
        window.ui = ui;
    };
</script>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>