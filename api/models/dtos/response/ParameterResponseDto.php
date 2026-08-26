<?php

namespace app\models\entities;

/**
 * DTO параметра сайта.
 *
 * @SWG\Definition(definition="ParameterResponseDto", required={"code", "title", "value"},
 *     @SWG\Property(property="code", type="string", description="Системный код"),
 *     @SWG\Property(property="title", type="string", description="Наименование"),
 *     @SWG\Property(property="value", type="string", description="Значение")
 * )
 */
class ParameterResponseDto
{
    /** Наименование */
    public string $code;

    /** Наименование */
    public string $title;

    /** Значение */
    public string $value;


    public function __construct(string $code, string $title, string $value)
    {
        $this->code = $code;
        $this->title = $title;
        $this->value = $value;
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'title' => $this->title,
            'value' => $this->value,
        ];
    }

    /**
     * Фабричный метод для создания DTO из модели ParameterEntity.
     *
     * @param ParameterEntity $entity
     * @return self
     */
    public static function createFromEntity(ParameterEntity $entity): self
    {
        return new self(
            (string) ($entity->code ?? $entity->title), // Если code пустой — используем title как fallback
            (string) $entity->title,
            (string) $entity->value,
        );
    }
}
