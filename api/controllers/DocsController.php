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
    public $layout = false;

    public function beforeAction($action)
    {
        if ($action->id !== 'json-schema') {
            Yii::$app->response->format = Response::FORMAT_HTML;
        }
        return parent::beforeAction($action);
    }

    /**
     * GET /docs — Swagger UI с persistAuthorization
     */
    public function actionIndex()
    {
        return $this->render('@app/views/docs/swagger-ui', [
            'restUrl' => Url::to(['docs/json-schema'], true),
        ]);
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

        $data['security'] = [
            ['Bearer' => []],
        ];

        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}