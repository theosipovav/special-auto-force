<?php


namespace app\controllers\api;

use app\dtos\response\SigninResponsDto;
use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;
use app\dtos\request\LoginForm;
use app\dtos\request\SignupForm;
use app\entities\UserEntity;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Публичный REST API контроллер аутентификации и профиля пользователя (JWT REST API).
 *
 * @SWG\Tag(
 *     name="public / auth controller",
 *     description="Аутентификация, регистрация и управление профилем пользователя."
 * )
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
     * Авторизация пользователя.
     *
     * @SWG\Post(
     *     path="/auth/login",
     *     tags={"public / auth controller"},
     *     operationId="authLogin",
     *     summary="Авторизация пользователя",
     *     description="Вход в систему по логину/email и паролю с получением JWT токена.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/LoginForm")),
     *
     *     @SWG\Response(response=200, description="Успешный вход", @SWG\Schema(ref="#/definitions/SigninResponsDto")),
     *     @SWG\Response(response=422, description="Ошибка валидации (неверный логин/пароль или аккаунт заблокирован)"),
     *     @SWG\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function actionLogin()
       {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');


        if (!$model->validate()) {
            $errorString = json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Yii::error('Ошибка валидации LoginForm: ' . $errorString, __METHOD__);
            throw new UnprocessableEntityHttpException('Ошибка валидации: ' . $errorString);
        }

        /** @var UserEntity $user */
        $user = $model->getUser();
        
        $token = $user->generateAccessToken();
        $expiresIn = Yii::$app->params['jwtExpire'] ?? (86400 * 7);

        Yii::$app->response->statusCode = 200;
        return new SigninResponsDto($token, 'Bearer', $expiresIn, $user->toArray());
    }

    /**
     * Регистрация нового пользователя.
     *
     * @SWG\Post(
     *     path="/auth/signup",
     *     tags={"public / auth controller"},
     *     operationId="authSignup",
     *     summary="Регистрация нового пользователя",
     *     description="Создание нового пользователя с автоматической авторизацией и выдачей JWT токена. Автоматически назначается роль 'customer'.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/SignupForm")),
     *
     *     @SWG\Response(response=200, description="Успешная регистрация", @SWG\Schema(ref="#/definitions/SigninResponsDto")),
     *     @SWG\Response(response=422, description="Ошибка валидации данных (логин/email занят, пароли не совпадают и т.д.)"),
     *     @SWG\Response(response=500, description="Ошибка при регистрации")
     * )
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

        Yii::error('SignupForm::signup returned null', __METHOD__);
        throw new ServerErrorHttpException('Ошибка при регистрации.');
    }

    /**
     * Получить данные текущего пользователя.
     *
     * @SWG\Get(
     *     path="/auth/me",
     *     tags={"public / auth controller"},
     *     operationId="authMe",
     *     summary="Получить данные текущего пользователя",
     *     description="Возвращает информацию об авторизованном пользователе. Требуется JWT токен.",
     *     produces={"application/json"},
     *     security={{"Bearer":{}}},
     *
     *     @SWG\Response(response=200, description="Данные пользователя", @SWG\Schema(ref="#/definitions/UserEntity")),
     *     @SWG\Response(response=401, description="Пользователь не авторизован")
     * )
     */
    public function actionMe()
    {
        /** @var UserEntity $user */
        $user = Yii::$app->user->identity;
        if (!$user) {
            throw new UnauthorizedHttpException('Пользователь не авторизован.');
        }
        return $user->toArray();
    }

    /**
     * Обновить JWT токен.
     *
     * @SWG\Post(
     *     path="/auth/refresh",
     *     tags={"public / auth controller"},
     *     operationId="authRefresh",
     *     summary="Обновить JWT токен",
     *     description="Обновление токена авторизации для текущего пользователя. Требуется действительный JWT токен.",
     *     produces={"application/json"},
     *     security={{"Bearer":{}}},
     *
     *     @SWG\Response(response=200, description="Новый токен", @SWG\Schema(ref="#/definitions/SigninResponsDto")),
     *     @SWG\Response(response=401, description="Пользователь не авторизован")
     * )
     */
    public function actionRefresh()
    {
        /** @var UserEntity $user */
        $user = Yii::$app->user->identity;
        if (!$user) {
            throw new UnauthorizedHttpException('Пользователь не авторизован.');
        }
        $newToken = $user->generateAccessToken();
        $expiresIn = Yii::$app->params['jwtExpire'] ?? (86400 * 7);
        return new SigninResponsDto($newToken, 'Bearer', $expiresIn, $user->toArray());
    }

}
