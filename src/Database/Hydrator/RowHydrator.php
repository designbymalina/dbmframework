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
 * How to use in Repository:
 * $row = $this->database->fetch($sql, $params);
 * return $this->database->hydrate($row);
 * or if you have your own DTO/VO class:
 * return $this->database->hydrate($row, \App\Dto\UserRow::class);
 */

declare(strict_types=1);

namespace Dbm\Database\Hydrator;

use Dbm\Database\Model\DataObject;
use InvalidArgumentException;

final class RowHydrator
{
    public function __construct(
        private readonly SmartHydrator $smartHydrator
    ) {}

    /**
     * Hydrate associative array into object.
     *
     * If no class is provided, a generic DataObject is returned.
     * If a class is provided, a new instance is created and hydrated.
     *
     * @param array<string, mixed>|null $row
     */
    public function hydrate(?array $row, ?string $class = null): ?object
    {
        if ($row === null) {
            return null;
        }

        if ($class === null) {
            return $this->hydrateDataObject($row);
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" does not exist.', $class)
            );
        }

        return $this->smartHydrator->hydrate($row, $class);
    }

    /**
     * Hydrate multiple associative arrays into objects.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, object|null>
     */
    public function hydrateAll(array $rows, ?string $class = null): array
    {
        return array_map(fn(array $row) => $this->hydrate($row, $class), $rows);
    }

    /**
     * Hydrate row into generic DataObject.
     *
     * @param array<string, mixed> $row
     */
    private function hydrateDataObject(array $row): DataObject
    {
        $object = new DataObject();
        $object->fill($row);

        return $object;
    }
}
