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

use Dbm\Core\Paths;
use Dbm\Infrastructure\Filesystem\Filesystem;

final class InstallationGuard
{
    private string $lockFile;

    private const LOCK_TTL = 300; // 5 min

    public function __construct(
        private Filesystem $filesystem
    ) {
        $this->lockFile = Paths::joinPaths(
            Paths::basePath(),
            'storage',
            'framework',
            'installing.lock'
        );
    }

    public function start(string $key): void
    {
        if ($this->filesystem->isFile($this->lockFile)) {

            $data = json_decode(
                $this->filesystem->readFile($this->lockFile),
                true
            );

            $started = strtotime($data['started_at'] ?? 'now');

            if (time() - $started < self::LOCK_TTL) {

                $module = $data['key'] ?? 'unknown';

                throw new \RuntimeException(
                    "Instalacja modułu '{$module}' jest w toku."
                );
            }

            // lock expired
            $this->filesystem->deleteFile($this->lockFile);
        }

        $this->filesystem->saveFile(
            $this->lockFile,
            json_encode([
                'key' => $key,
                'started_at' => date('c'),
            ])
        );
    }

    public function finish(): void
    {
        if ($this->filesystem->isFile($this->lockFile)) {
            $this->filesystem->deleteFile($this->lockFile);
        }
    }

    public function isLocked(): bool
    {
        return $this->filesystem->isFile($this->lockFile);
    }

    public function forceUnlock(): void
    {
        $this->finish();
    }
}
