<?php

namespace app\models\dto\response;

use app\models\entities\Role;
use app\models\entities\UserRole;

/**
 * DTO для ответа с данными пользователя
 */
class UserResponseDto
{
    public int $id;
    public string $username;
    public string $email;
    public string $phone;
    public ?string $address;
    public string $name;
    public string $surname;
    public ?string $patronymic;
    public ?string $date_of_birth;
    public ?string $image;
    public string $date_of_registration;
    public bool $active;
    public array $userRoles;
    public array $roles;

    /**
     * @param int $id Id
     * @param string $username Логин (UserName)
     * @param string $email Адрес электронной почты (Email)
     * @param string $phone Телефон (Phone)
     * @param string|null $address Адрес (Address)
     * @param string $name Имя (Name)
     * @param string $surname Фамилия (Surname)
     * @param string|null $patronymic Отчество (Patronymic)
     * @param string|null $date_of_birth Дата рождения (DateOfBirth)
     * @param string|null $image Фото (Image) ссылка на аватар через ImageEntity
     * @param string $date_of_registration Дата регистрации (DatOfRegistration)
     * @param bool $active Активен
     * @param UserRole[] $userRoles Массив UserRole
     * @param Role[] $roles Массив Role
     */
    public function __construct(
        int $id,
        string $username,
        string $email,
        string $phone,
        ?string $address,
        string $name,
        string $surname,
        ?string $patronymic,
        ?string $date_of_birth,
        ?string $image,
        string $date_of_registration,
        bool $active,
        array $userRoles,
        array $roles
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->name = $name;
        $this->surname = $surname;
        $this->patronymic = $patronymic;
        $this->date_of_birth = $date_of_birth;
        $this->image = $image;
        $this->date_of_registration = $date_of_registration;
        $this->active = $active;
        $this->userRoles = $userRoles;
        $this->roles = $roles;
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'name' => $this->name,
            'surname' => $this->surname,
            'patronymic' => $this->patronymic,
            'date_of_birth' => $this->date_of_birth,
            'image' => $this->image,
            'date_of_registration' => $this->date_of_registration,
            'active' => $this->active,
            'userRoles' => array_map(fn($ur) => $ur instanceof UserRole ? ['user_id' => $ur->user_id, 'role_id' => $ur->role_id] : $ur, $this->userRoles),
            'roles' => array_map(fn($r) => $r instanceof Role ? ['id' => $r->id, 'title' => $r->title] : $r, $this->roles),
        ];
    }
}
