<?php

namespace app\models\dtos\request;

use yii\base\Model;

/**
 * DTO для установки нового пароля пользователю
 *
 * @SWG\Definition(
 *     definition="PasswordSetRequestDto",
 *     type="object",
 *     description="DTO для установки нового пароля",
 *     required={"password", "passwordRepeat"},
 *
 *     @SWG\Property(property="password", type="string", minLength=6, description="Новый пароль (минимум 6 символов)", example="newSecurePassword123"),
 *     @SWG\Property(property="passwordRepeat", type="string", description="Повтор нового пароля", example="newSecurePassword123")
 * )
 */
class PasswordSetRequestDto extends Model
{
    public string $password;
    public string $passwordRepeat;

    public function rules()
    {
        return [
            [['password', 'passwordRepeat'], 'required', 'message' => 'Поле "{attribute}" обязательно для заполнения'],
            ['password', 'string', 'min' => 6, 'message' => 'Пароль должен содержать минимум 6 символов'],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Новый пароль',
            'passwordRepeat' => 'Повтор пароля',
        ];
    }
}