<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @SWG\Info(version="1.0.0", title="Special Auto Force API", description="REST API on Yii2")
 * )
 */
class DocsController extends Controller
{
    public function beforeAction($action)
    {
        if ($action->id === 'json-schema') {
            // формат выставим в самом action
        } else {
            Yii::$app->response->format = Response::FORMAT_HTML;
        }
        return parent::beforeAction($action);
    }

    public function actions(): array
    {
        return [
            'index' => [
                'class' => 'yii2mod\swagger\SwaggerUIRenderer',
                'restUrl' => Url::to(['docs/json-schema'], true),
            ],
            // json-schema делаем своим методом, не через OpenAPIRenderer
        ];
    }

    /**
     * GET /docs/json-schema
     */
    public function actionJsonSchema()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        $swagger = \Swagger\scan([
            Yii::getAlias('@app/controllers'),
            Yii::getAlias('@app/models'),
        ]);

        // swagger-php 2.x: json_encode / (string), не toJson()
        $data = json_decode(json_encode($swagger), true);
        if (!is_array($data)) {
            $data = json_decode((string) $swagger, true) ?: [];
        }

        $data['securityDefinitions'] = [
            'Bearer' => [
                'type' => 'apiKey',
                'name' => 'Authorization',
                'in' => 'header',
                'description' => 'Вставь: Bearer <jwt>  (слово Bearer, пробел, токен)',
            ],
        ];

        // Все методы по умолчанию требуют Bearer
        $data['security'] = [
            ['Bearer' => []],
        ];

        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}
