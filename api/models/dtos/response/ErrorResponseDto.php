<?php

namespace app\models\dtos\response;

class ErrorResponseDto
{
    public $success;
    public $message;
    public $data;


    public function __construct($message, $data)
    {
        $this->success = false;
        $this->message = $message;
        $this->data = $data;
    }
}

