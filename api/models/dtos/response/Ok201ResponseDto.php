<?php

namespace app\models\dtos\response;

/**
 * Универсальный ответ с сообщением.
 *
 * @SWG\Definition(
 *     definition="Ok201ResponseDto",
 *     required={"message"},
 *
 *     @SWG\Property(
 *         property="message",
 *         type="string",
 *         description="Текст сообщения"
 *     ),
 *     @SWG\Property(
 *         property="data",
 *         type="object",
 *         description="Содержимое ответа"
 *     )
 * )
 */
class Ok201ResponseDto
{
    /**
     * Текст сообщения
     */
    public $message;

    /**
     * Содержимое
     */
    public $data;


    public function __construct($message, $data = null)
    {
        $this->message = $message;
        $this->data = $data;
    }
}

