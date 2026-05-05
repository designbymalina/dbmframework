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
 * Example of usage:
 *
 * $manager = new BundleManager();
 * $manager->add(
 *     'Dbm\\SearchEngine\\',
 *     Paths::joinPaths(Paths::basePath(), 'libraries/designbymalina/searchengine/src')
 * );
 */

declare(strict_types=1);

namespace Dbm\Core\Bundles;

use Dbm\Core\Paths;

final class BundleManager
{
    private string $file;

    public function __construct()
    {
        $this->file = Paths::joinPaths(Paths::basePath(), 'storage', 'framework', 'bundles.php');
    }

    public function add(string $namespace, string $path): void
    {
        $bundles = file_exists($this->file)
            ? require $this->file
            : [];

        $bundles[$namespace] = $path;

        $this->write($bundles);
    }

    /**
     * @param array<string, string> $bundles
     */
    private function write(array $bundles): void
    {
        $content = "<?php\n\nreturn " . var_export($bundles, true) . ";\n";

        file_put_contents($this->file, $content);
    }
}
