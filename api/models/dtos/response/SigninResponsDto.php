<?php

namespace app\models\dtos\response;

/**
 * Ответ авторизации пользователя.
 *
 * @SWG\Definition(
 *     definition="SigninResponsDto",
 *     required={
 *         "token",
 *         "tokenType",
 *         "expiresIn",
 *         "user"
 *     },
 *
 *     @SWG\Property(
 *         property="token",
 *         type="string",
 *         description="JWT токен доступа"
 *     ),
 *     @SWG\Property(
 *         property="tokenType",
 *         type="string",
 *         example="Bearer",
 *         description="Тип токена"
 *     ),
 *     @SWG\Property(
 *         property="expiresIn",
 *         type="integer",
 *         description="Время жизни токена в секундах"
 *     ),
 *     @SWG\Property(
 *         property="user",
 *         ref="#/definitions/UserResponseDto",
 *         description="Данные авторизованного пользователя"
 *     )
 * )
 */
class SigninResponsDto
{
    public $token;
    public $tokenType;
    public $expiresIn;
    public $user;


    public function __construct($token, $tokenType, $expiresIn, $user)
    {
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->user = $user;

    }
}

