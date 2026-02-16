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

namespace Dbm\Core\Module\Service;

use Dbm\Core\Module\Repository\InstallRepository;

final class DatabaseMigrationService
{
    public function __construct(
        private InstallRepository $repository
    ) {}

    public function migrate(array $files, string $packageRoot): void
    {
        if (!$this->repository->isConnected()) {
            if (!$this->repository->connect()) {
                throw new \RuntimeException('Database connection failed');
            }
        }

        $db = $this->repository->getDatabase();

        if (method_exists($db, 'beginTransaction')) {
            $db->beginTransaction();
        }

        try {
            foreach ($files as $file) {
                $path = $packageRoot . '/' . ltrim($file, '/');

                if (!is_file($path)) {
                    throw new \RuntimeException("Migration file missing: {$file}");
                }

                if (!$this->hasExecutableSql($path)) {
                    continue;
                }

                $success = $this->repository->importDataFromFile($path);

                if (!$success) {
                    throw new \RuntimeException("Database migration failed: {$file}");
                }
            }

            if (method_exists($db, 'inTransaction') && $db->inTransaction()) {
                $db->commit();
            }
        } catch (\Throwable $e) {
            if (method_exists($db, 'inTransaction') && $db->inTransaction()) {
                $db->rollback();
            }
            throw $e;
        }
    }

    private function hasExecutableSql(string $path): bool
    {
        $content = file_get_contents($path);

        // Usuń komentarze SQL
        $content = preg_replace('/--.*$/m', '', $content);
        $content = trim($content);

        return $content !== '';
    }
}
