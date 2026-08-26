<?php


namespace app\controllers\api;

use app\models\dtos\response\SigninResponsDto;
use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;
use app\models\dtos\request\LoginForm;
use app\models\dtos\request\SignupForm;
use app\models\entities\UserEntity;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Контроллер аутентификации и профиля пользователя (JWT REST API)
 * 
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
     *     summary="Авторизация пользователя",
     *     description="Вход в систему по логину/email и паролю с получением JWT токена",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             required={"username", "password"},
     *             @SWG\Property(property="username", type="string", description="Логин или E-mail пользователя", example="user@example.com"),
     *             @SWG\Property(property="password", type="string", format="password", description="Пароль пользователя", example="password123")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Успешный вход",
     *         @SWG\Schema(
     *             @SWG\Property(property="token", type="string", description="JWT токен доступа"),
     *             @SWG\Property(property="tokenType", type="string", description="Тип токена", example="Bearer"),
     *             @SWG\Property(property="expiresIn", type="integer", description="Время жизни токена в секундах", example=604800),
     *             @SWG\Property(property="user", type="object", description="Данные пользователя")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Пользователь не найден",
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Неверный пароль",
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *     ),
     * )
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        // 1. Проверяем обязательные поля (username и password)
        if (!$model->validate()) {
            throw new ServerErrorHttpException('Проверьте правильность заполнения данных и повторите запрос.');
        }

        // 2. Ищем пользователя по username или email
        $user = UserEntity::find()
            ->where(['username' => $model->username])
            ->orWhere(['email' => $model->username])
            ->one();

        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        // 3. Проверяем пароль
        if (!$user->validatePassword($model->password)) {
            throw new UnauthorizedHttpException('Введен не верный пароль.');
        }

        // 4. Успех – генерируем токен
        $token = $user->generateAccessToken();
        $expiresIn = Yii::$app->params['jwtExpire'] ?? (86400 * 7);

        Yii::$app->response->statusCode = 200;
        $res = new SigninResponsDto($token, 'Bearer', $expiresIn, $user->toArray());
        return $res;
    }

    /**
     * @SWG\Post(
     *     path="/auth/signup",
     *     tags={"Auth"},
     *     summary="Регистрация нового пользователя",
     *     description="Создание нового пользователя с автоматической авторизацией и выдачей JWT токена",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             required={"userName", "password", "email", "phone", "name", "surname"},
     *             @SWG\Property(property="userName", type="string", description="Логин пользователя", example="newuser"),
     *             @SWG\Property(property="password", type="string", format="password", description="Пароль (минимум 6 символов)", example="password123"),
     *             @SWG\Property(property="email", type="string", format="email", description="E-mail адрес", example="user@example.com"),
     *             @SWG\Property(property="phone", type="string", description="Телефон", example="+79991234567"),
     *             @SWG\Property(property="name", type="string", description="Имя", example="Иван"),
     *             @SWG\Property(property="surname", type="string", description="Фамилия", example="Иванов"),
     *             @SWG\Property(property="patronymic", type="string", description="Отчество", example="Иванович"),
     *             @SWG\Property(property="dateOfBirth", type="string", format="date", description="Дата рождения", example="1990-01-01"),
     *             @SWG\Property(property="address", type="string", description="Адрес", example="г. Москва, ул. Ленина 1")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Успешная регистрация",
     *         @SWG\Schema(ref="#/definitions/SigninResponsDto")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Ошибка при регистрации",
     *     )
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
     * @SWG\Get(
     *     path="/auth/me",
     *     tags={"Auth"},
     *     summary="Получить данные текущего пользователя",
     *     description="Возвращает информацию об авторизованном пользователе. Требуется JWT токен.",
     *     produces={"application/json"},
     *     security={{"Bearer":{}}},
     *     @SWG\Response(
     *         response=200,
     *         description="Данные пользователя",
     *         @SWG\Schema(ref="#/definitions/UserResponseDto")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *     )
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
     * @SWG\Post(
     *     path="/auth/refresh",
     *     tags={"Auth"},
     *     summary="Обновить JWT токен",
     *     description="Обновление токена авторизации для текущего пользователя. Требуется действительный JWT токен.",
     *     produces={"application/json"},
     *     security={{"bearerAuth":{}}},
     *     @SWG\Response(
     *         response=200,
     *         description="Новый токен",
     *         @SWG\Schema(ref="#/definitions/SigninResponsDto")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *     )
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

    /**
     * @SWG\Options(
     *     path="/auth/options",
     *     tags={"Auth"},
     *     summary="CORS preflight запрос",
     *     description="Обработка OPTIONS запроса для CORS",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string", example="ok")
     *         )
     *     )
     * )
     */
    public function actionOptions()
    {
        return ['status' => 'ok'];
    }
}
