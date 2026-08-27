<?php

namespace app\controllers\api\admin;

use Yii;
use yii\data\ActiveDataProvider;
use app\entities\RoleEntity;

/**
 * Роли пользователей (администратор)
 *
 * @SWG\Tag(
 *     name="admin / role controller",
 *     description="Управление ролями пользователей."
 * )
 */
class RoleController extends BaseApiAdminController
{
    public $modelClass = RoleEntity::class;


    /**
     * Список ролей.
     *
     * GET /admin/roles
     *
     * @SWG\Get(
     *   tags={"admin / role controller"},
     *   path="/admin/roles",
     *   summary="Список ролей с фильтрацией по названию",
     *   description="Возвращает список ролей с пагинацией. Поддерживает фильтрацию по названию и expand=users,usersCount.",
     *   security={{"Bearer": {}}},
     *
     *   @SWG\Parameter(name="title", in="query", type="string", description="Фильтр по названию роли (LIKE %title%)"),
     *   @SWG\Parameter(name="expand", in="query", type="string", description="Дополнительные поля: users, usersCount (через запятую)"),
     *   @SWG\Parameter(name="page", in="query", type="integer", description="Номер страницы (по умолчанию 1)"),
     *   @SWG\Parameter(name="per-page", in="query", type="integer", description="Количество записей на странице (по умолчанию 50)"),
     *
     *   @SWG\Response(response=200, description="Успешный ответ",@SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @SWG\Items(ref="#/definitions/RoleEntity")),
     *       @SWG\Property(property="_links", type="object"),
     *       @SWG\Property(property="_meta", type="object")
     *   )),
     *   @SWG\Response(response=401, description="Не авторизован"),
     *   @SWG\Response(response=403, description="Доступ запрещён (недостаточно прав)")
     * )
     */
    public function actions()
    {
        $actions = parent::actions();

        // Разрешаем только стандартный index
        $allowed = ['index'];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }

        $actions['index']['prepareDataProvider'] = function () {
            $query = RoleEntity::find();

            $title = Yii::$app->request->get('title');
            if (!empty($title)) {
                $query->andWhere(['like', 'title', $title]);
            }

            return new ActiveDataProvider([
                'query' => $query->orderBy(['id' => SORT_ASC]),
                'pagination' => [
                    'pageSize' => 50,
                    'pageSizeParam' => 'per-page',
                ],
            ]);
        };

        return $actions;
    }
}
