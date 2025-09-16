<?php
namespace App\Data\Entities\Traits;

trait Creatable
{
    public static function createEmpty(): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new static();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $args[] = self::getParameterDefaultValue($param);
        }

        return $reflection->newInstanceArgs($args);
    }

    private static function getParameterDefaultValue(\ReflectionParameter $param): mixed
    {
        // Если есть значение по умолчанию - используем его
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        // Получаем тип параметра
        $type = $param->getType();

        // Если тип не указан, возвращаем null
        if ($type === null) {
            return null;
        }

        // Проверяем, является ли тип обнуляемым
        $isNullable = $type->allowsNull();

        // Получаем имя типа (для NamedType)
        $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : null;

        // Для обнуляемых типов возвращаем null
        if ($isNullable) {
            return null;
        }

        // Для НЕ-обнуляемых типов возвращаем значения по умолчанию
        if ($typeName !== null) {
            return match ($typeName) {
                'int' => 0,
                'float' => 0.0,
                'string' => '',
                'bool' => false,
                'array' => [],
                default => null
            };
        }

        return null;
    }
}
