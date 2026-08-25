<?php


namespace app\controllers\api;

use app\models\dtos\response\ErrorResponseDto;
use app\models\dtos\response\SigninResponsDto;
use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;
use app\models\dtos\request\LoginForm;
use app\models\dtos\request\SignupForm;
use app\models\entities\User;

/**
 * Контроллер аутентификации и профиля пользователя (JWT REST API)
 */
class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 1. JSON Response
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;

        // 2. CORS
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
            ],
        ];

        // 3. Bearer Auth only for me and refresh actions
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['me', 'refresh'],
        ];

        return $behaviors;
    }

    /**
     * @SWG\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Авторизация",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             required={"email", "password"},
     *             @SWG\Property(property="email", type="string"),
     *             @SWG\Property(property="password", type="string")
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Успешный вход"),
     *     @SWG\Response(response=401, description="Неверные данные")
     * )
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        // 1. Проверяем обязательные поля (username и password)
        if (!$model->validate()) {
            Yii::$app->response->statusCode = 400;
            return new ErrorResponseDto('Ошибка валидации данных', $model->getErrors());
        }

        // 2. Ищем пользователя по username или email
        $user = User::find()
            ->where(['username' => $model->username])
            ->orWhere(['email' => $model->username])
            ->one();

        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return new ErrorResponseDto('Пользователь не найден', null);
        }

        // 3. Проверяем пароль
        if (!$user->validatePassword($model->password)) {
            Yii::$app->response->statusCode = 403;
            return new ErrorResponseDto('Не верный пароль', null);
        }


        // 4. Успех – генерируем токен
        $token = $user->generateAccessToken();
        $expiresIn = Yii::$app->params['jwtExpire'] ?? (86400 * 7);

        Yii::$app->response->statusCode = 200;
        $res = new SigninResponsDto($token, 'Bearer', $expiresIn, $user->toArray());
        return $res;
    }


    /**
     * POST /api/auth/signup
     * Регистрация нового пользователя
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        $user = $model->signup();
        if ($user) {
            $token = $user->generateAccessToken();
            $expiresIn = Yii::$app->params['jwtExpire'] ?? (86400 * 7);
            return new SigninResponsDto($token, 'Bearer', $expiresIn, $user->toArray());
        }
        Yii::$app->response->statusCode = 422;
        return new ErrorResponseDto('Ошибка при регистрации', $model->getErrors());
    }

    /**
     * GET /api/auth/me
     * Получить информацию о текущем авторизованном пользователе
     */
    public function actionMe()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if (!$user) {
            Yii::$app->response->statusCode = 403;
            return new ErrorResponseDto('Пользователь не авторизован.', null);
        }
        return $user->toArray();
    }

    /**
     * POST /api/auth/refresh
     * Обновить JWT токен авторизации
     */
    public function actionRefresh()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if (!$user) {
            Yii::$app->response->statusCode = 403;
            return new ErrorResponseDto('Пользователь не авторизован.', null);
        }
        $newToken = $user->generateAccessToken();
        $expiresIn = Yii::$app->params['jwtExpire'] ?? (86400 * 7);
        return new SigninResponsDto($newToken, 'Bearer', $expiresIn, $user->toArray());
    }

    /**
     * Handle preflight OPTIONS
     */
    public function actionOptions()
    {
        return ['status' => 'ok'];
    }
}
