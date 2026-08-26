<?php

namespace app\controllers\api;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\entities\ParameterEntity;
use app\models\entities\ParameterResponseDto;

/**
 * Публичный REST API контроллер параметров сайта.
 *
 * Все методы доступны без авторизации любому пользователю.
 * Предоставляет доступ к системным параметрам сайта (настройки, контакты и т.д.).
 *
 * @SWG\Tag(
 *     name="public / parameter controller",
 *     description="Публичный доступ к параметрам сайта."
 * )
 */
class ParameterController extends BaseApiController
{
    public $modelClass = ParameterEntity::class;

    /**
     * Отключаем стандартные CRUD-действия родителя (create/update/delete/view),
     * так как публичный контроллер предоставляет только чтение.
     */
    public function actions()
    {
        return [];
    }

    /**
     * Получить словарь всех параметров сайта.
     *
     * @SWG\Get(
     *     path="/parameters",
     *     tags={"public / parameter controller"},
     *     operationId="publicParameterIndex",
     *     summary="Словарь всех параметров сайта",
     *     description="Возвращает ассоциативный массив всех параметров в формате [code => value]. Если у параметра отсутствует code, в качестве ключа используется title.",
     *     produces={"application/json"},
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(type="object", description="Ассоциативный массив параметров [code => value]", additionalProperties=@SWG\Property(type="string")))
     * )
     */
    public function actionIndex()
    {
        Yii::$app->response->statusCode = 200;
        return ParameterEntity::getMap();
    }

    /**
     * Получить параметр по его системному коду.
     *
     * @SWG\Get(
     *     path="/parameters/find-by-code",
     *     tags={"public / parameter controller"},
     *     operationId="publicParameterFindByCode",
     *     summary="Получить параметр по коду",
     *     description="Возвращает параметр сайта по его системному коду в виде ParameterResponseDto.",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(name="code", in="query", type="string", required=true, description="Системный код параметра"),
     *
     *     @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(ref="#/definitions/ParameterResponseDto")),
     *     @SWG\Response(response=400, description="Не указан параметр code"),
     *     @SWG\Response(response=404, description="Параметр с указанным кодом не найден")
     * )
     */
    public function actionFindByCode()
    {
        $code = Yii::$app->request->get('code');

        if (empty($code)) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'Не указан обязательный параметр "code".',
            ];
        }

        /** @var ParameterEntity|null $parameter */
        $parameter = ParameterEntity::find()
            ->where(['code' => $code])
            ->one();

        if (!$parameter) {
            throw new NotFoundHttpException("Параметр с кодом '{$code}' не найден.");
        }

        Yii::$app->response->statusCode = 200;
        return ParameterResponseDto::createFromEntity($parameter)->toArray();
    }
}
