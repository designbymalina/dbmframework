<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * Smart object hydrator.
 *
 * Features:
 * - snake_case -> camelCase mapping
 * - scalar type casting
 *
 * @TODO
 * - DateTime hydration
 * - Enum hydration
 * - PHP Attributes
 * - Custom casters
 * - Reflection cache
 */

declare(strict_types=1);

namespace Dbm\Database\Hydrator;

use ReflectionClass;
use ReflectionNamedType;

final class SmartHydrator
{
    /**
     * @var array<class-string, ReflectionClass<object>>
     */
    private array $reflectionCache = [];

    /**
     * @param array<string,mixed> $row
     */
    public function hydrate(array $row, string $class): object
    {
        $instance = new $class();
        $reflection = $this->reflection($class);

        foreach ($row as $column => $value) {
            $property = NameMapper::toCamelCase($column);

            if (!$reflection->hasProperty($property)) {
                // @INFO Opcjonalny tryb ścisły / rejestrowanie debugowania (logger).
                continue;
            }

            $refProperty = $reflection->getProperty($property);

            $type = $refProperty->getType();

            if ($type instanceof ReflectionNamedType) {
                $value = $this->castValue($value, $type->getName());
            }

            if (!$refProperty->isPublic()) {
                continue;
            }

            $instance->{$property} = $value;
        }

        return $instance;
    }

    /**
     * @param class-string $class
     * @return ReflectionClass<object>
     */
    private function reflection(string $class): ReflectionClass
    {
        return $this->reflectionCache[$class] ??= new ReflectionClass($class);
    }

    private function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'string' => (string) $value,
            'array' => (array) $value,
            default => $value,
        };
    }
}
