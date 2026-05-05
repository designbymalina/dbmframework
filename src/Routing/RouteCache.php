<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Routing;

final class RouteCache
{
    public function __construct(
        private readonly string $file
    ) {}

    /**
     * @param array<string> $sources
     */
    public function isFresh(array $sources): bool
    {
        if (!file_exists($this->file)) {
            return false;
        }

        $cacheTime = filemtime($this->file);

        foreach ($sources as $file) {
            if (filemtime($file) > $cacheTime) {
                return false;
            }
        }

        return true;
    }

    public function write(RouteCollection $routes): void
    {
        $data = $routes->export();

        file_put_contents(
            $this->file,
            "<?php\nreturn " . var_export($data, true) . ";\n"
        );
    }

    /**
     * @return array<string, Route>
     */
    public function load(): array
    {
        return require $this->file;
    }
}
