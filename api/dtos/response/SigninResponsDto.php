<?php

namespace app\dtos\response;

/**
 * Ответ авторизации пользователя.
 *
 * @SWG\Definition(
 *     definition="SigninResponsDto",
 *     required={"token", "tokenType", "expiresIn", "user"},
 *
 *     @SWG\Property(property="token", type="string", description="JWT токен доступа"),
 *     @SWG\Property(property="tokenType", type="string", example="Bearer", description="Тип токена"),
 *     @SWG\Property(property="expiresIn", type="integer", description="Время жизни токена в секундах", example=604800),
 *     @SWG\Property(property="user", ref="#/definitions/UserEntity", description="Данные авторизованного пользователя")
 * )
 */
class SigninResponsDto
{
    public string $token;
    public string $tokenType;
    public int $expiresIn;
    public array $user;


   public function __construct(string $token, string $tokenType, int $expiresIn, array $user)
    {
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->user = $user;
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'tokenType' => $this->tokenType,
            'expiresIn' => $this->expiresIn,
            'user' => $this->user,
        ];
    }
}
