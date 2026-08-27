<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use app\entities\ParameterEntity;

/**
 * Параметры сайта (администратор)
 *
 * @SWG\Tag(
 *     name="admin / parameter controller",
 *     description="CRUD параметров сайта (ParameterEntity)."
 * )
 */
class ParameterController extends BaseApiAdminController
{
    public $modelClass = ParameterEntity::class;

    /**
     * @SWG\Get(
     *   tags={"admin / parameter controller"},
     *   path="/admin/parameters",
     *   summary="Список параметров",
     *   description="Список параметров с пагинацией и фильтрами по group, code, pageId и общим поиском.",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="group", in="query", type="string", description="Фильтр по группе"),
     *   @SWG\Parameter(name="code", in="query", type="string", description="Фильтр по системному коду"),
     *   @SWG\Parameter(name="pageId", in="query", type="integer", description="Фильтр по ID страницы"),
     *   @SWG\Parameter(name="q", in="query", type="string", description="Поиск по title, value, code, group"),
     *   @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы (по умолчанию 1)"),
     *   @SWG\Parameter(name="per-page", in="query", type="integer", description="Записей на странице (по умолчанию 50)"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ",@SWG\Schema(
     *     type="object",
     *     @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/ParameterEntity")),
     *     @SWG\Property(property="_links", type="object"),
     *     @SWG\Property(property="_meta", type="object")
     *   )),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     *
     * @SWG\Get(
     *   tags={"admin / parameter controller"},
     *   path="/admin/parameter/{id}",
     *   summary="Получить параметр по ID",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID параметра"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ", @SWG\Schema(ref="#/definitions/ParameterEntity")),
     *   @SWG\Response(response=404, description="Параметр не найден"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     *
     * @SWG\Put(
     *   tags={"admin / parameter controller"},
     *   path="/admin/parameter/{id}",
     *   summary="Обновить параметр",
     *   description="Обновляет поля title, value, code, group, pageId.",
     *   security={{"Bearer": {}}},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID параметра"),
     *   @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(
     *     type="object",
     *     @SWG\Property(property="title", type="string", maxLength=255),
     *     @SWG\Property(property="value", type="string"),
     *     @SWG\Property(property="code", type="string", maxLength=64),
     *     @SWG\Property(property="group", type="string", maxLength=64),
     *     @SWG\Property(property="pageId", type="integer")
     *   )),
     *
     *   @SWG\Response(response=200, description="Параметр обновлён", @SWG\Schema(ref="#/definitions/ParameterEntity")),
     *   @SWG\Response(response=404, description="Параметр не найден"),
     *   @SWG\Response(response=422, description="Ошибка валидации"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     *
     * @SWG\Delete(
     *   tags={"admin / parameter controller"},
     *   path="/admin/parameter/{id}",
     *   summary="Удалить параметр",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="id", in="path", required=true, type="integer", description="ID параметра"),
     *
     *   @SWG\Response(response=204, description="Параметр успешно удалён"),
     *   @SWG\Response(response=404, description="Параметр не найден"),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён")
     * )
     */
    public function actions()
    {
        $actions = parent::actions();

        $allowed = ['index', 'view', 'update', 'delete'];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                unset($actions[$key]);
            }
        }

        $actions['index']['prepareDataProvider'] = function () {
            $query = ParameterEntity::find();

            $group = Yii::$app->request->get('group');
            if ($group !== null && $group !== '') {
                $query->andWhere(['group' => $group]);
            }

            $code = Yii::$app->request->get('code');
            if ($code !== null && $code !== '') {
                $query->andWhere(['code' => $code]);
            }

            $pageId = Yii::$app->request->get('pageId');
            if ($pageId !== null && $pageId !== '') {
                $query->andWhere(['pageId' => (int) $pageId]);
            }

            $q = Yii::$app->request->get('q');
            if (!empty($q)) {
                $query->andFilterWhere([
                    'or',
                    ['like', 'title', $q],
                    ['like', 'value', $q],
                    ['like', 'code', $q],
                    ['like', 'group', $q],
                ]);
            }

            return new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 50,
                    'pageSizeParam' => 'per-page',
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        };

        return $actions;
    }
}