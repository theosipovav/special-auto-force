<?php

namespace app\models\dtos\response;

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

