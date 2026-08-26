<?php

namespace app\models\dtos\response;

use app\models\entities\UserEntity;
use app\models\entities\RoleEntity;
use app\models\entities\UserRoleEntity;

/**
 * DTO ответа с данными пользователя.
 *
 * @SWG\Definition(
 *     definition="UserResponseDto",
 *     required={
 *         "id",
 *         "username",
 *         "email",
 *         "phone",
 *         "name",
 *         "surname",
 *         "date_of_registration",
 *         "active",
 *         "userRoles",
 *         "roles"
 *     },
 *
 *     @SWG\Property(
 *         property="id",
 *         type="integer",
 *         description="Идентификатор пользователя"
 *     ),
 *     @SWG\Property(
 *         property="username",
 *         type="string",
 *         description="Логин пользователя"
 *     ),
 *     @SWG\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Адрес электронной почты"
 *     ),
 *     @SWG\Property(
 *         property="phone",
 *         type="string",
 *         description="Телефон пользователя"
 *     ),
 *     @SWG\Property(
 *         property="address",
 *         type="string",
 *         description="Адрес пользователя"
 *     ),
 *     @SWG\Property(
 *         property="name",
 *         type="string",
 *         description="Имя"
 *     ),
 *     @SWG\Property(
 *         property="surname",
 *         type="string",
 *         description="Фамилия"
 *     ),
 *     @SWG\Property(
 *         property="patronymic",
 *         type="string",
 *         description="Отчество"
 *     ),
 *     @SWG\Property(
 *         property="date_of_birth",
 *         type="string",
 *         format="date",
 *         description="Дата рождения"
 *     ),
 *     @SWG\Property(
 *         property="image",
 *         type="string",
 *         format="uri",
 *         description="Ссылка на изображение пользователя"
 *     ),
 *     @SWG\Property(
 *         property="date_of_registration",
 *         type="string",
 *         format="date-time",
 *         description="Дата регистрации"
 *     ),
 *     @SWG\Property(
 *         property="active",
 *         type="boolean",
 *         description="Активен ли пользователь"
 *     ),
 *     @SWG\Property(
 *         property="userRoles",
 *         type="array",
 *         description="Связи пользователя с ролями",
 *         @SWG\Items(
 *             ref="#/definitions/UserRoleEntity"
 *         )
 *     ),
 *     @SWG\Property(
 *         property="roles",
 *         type="array",
 *         description="Роли пользователя",
 *         @SWG\Items(
 *             ref="#/definitions/RoleEntity"
 *         )
 *     )
 * )
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
     * @param UserRoleEntity[] $userRoles Массив UserRoleEntity
     * @param RoleEntity[] $roles Массив RoleEntity
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
     * Создать DTO из модели пользователя.
     *
     * @param UserEntity $user
     * @return static
     */
    public static function fromUser(UserEntity $user): self
    {
        return new self(
            $user->id,
            $user->username,
            $user->email,
            $user->phone,
            $user->address,
            $user->name,
            $user->surname,
            $user->patronymic,
            $user->date_of_birth,
            $user->imageEntity ? $user->imageEntity->url : null,
            $user->date_of_registration,
            $user->status === UserEntity::STATUS_ACTIVE,
            $user->userRoles,   // массив объектов UserRoleEntity
            $user->roles        // массив объектов RoleEntity
        );
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
            'userRoles' => array_map(fn($ur) => $ur instanceof UserRoleEntity ? ['user_id' => $ur->user_id, 'role_id' => $ur->role_id] : $ur, $this->userRoles),
            'roles' => array_map(fn($r) => $r instanceof RoleEntity ? ['id' => $r->id, 'title' => $r->title] : $r, $this->roles),
        ];
    }
}
