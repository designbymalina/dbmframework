<?php

/**
 * Module: DbM DataTables
 * PHP library for efficient CRUD operations and high-performance database management.
 *
 * This software is proprietary and licensed.
 * Use of this software is subject to the terms of the DbM Platform License.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina
 * @license Proprietary
 *
 * @see /LICENSE_DBM_PLATFORM.txt
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

class RowHydrator
{
    /**
     * Hydrate row to object or to given class.
     *
     * - If $class is null -> returns stdClass with public props.
     * - If $class exists -> instantiate and set public properties (fallback: set via reflection if needed).
     *
     * @param array|null $row
     * @param string|null $class Fully qualified class name or null
     * @return object|null
     */
    public function hydrate(?array $row, ?string $class = null): ?object
    {
        if ($row === null) {
            return null;
        }

        if ($class === null) {
            $obj = new \stdClass();
            foreach ($row as $k => $v) {
                $obj->{$k} = $v;
            }
            return $obj;
        }

        if (!class_exists($class)) {
            // fallback to stdClass
            $obj = new \stdClass();
            foreach ($row as $k => $v) {
                $obj->{$k} = $v;
            }
            return $obj;
        }

        // If class exists, try to set public properties; if not possible, use reflection to set.
        $instance = new $class();

        // Try to set public properties directly
        foreach ($row as $k => $v) {
            // property exists and is public?
            if (property_exists($instance, $k)) {
                $refProp = new \ReflectionProperty($class, $k);
                if ($refProp->isPublic()) {
                    $instance->{$k} = $v;
                    continue;
                }
            }

            // fallback: set dynamically if allowed
            $instance->{$k} = $v;
        }

        return $instance;
    }
}
