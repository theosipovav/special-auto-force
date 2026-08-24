<?php

namespace app\models\dtos\response;

class OkResponseDto
{
    public $success;
    public $message;
    public $data;


    public function __construct($message, $data)
    {
        $this->success = true;
        $this->message = $message;
        $this->data = $data;
    }
}

