<?php

/**
 * Library: On-Page Advertising Handling System
 * A class designed for the DbM Framework and for use in any PHP application.
 *
 * @package Lib\AdvertisementCache
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Lib\Adverts;

final class AdvertisementCache
{
    private const ADS_DIR = 'data/adverts';
    private const CACHE_FILE = 'var/cache/adverts/ads_cache.php';

    private static ?self $instance = null;

    /** @var array<string, string> */
    private array $cache = [];

    private function __construct()
    {
        $this->preloadAds();
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function getAdvert(string $position, string $space = ''): string
    {
        $advert = $this->cache[$position] ?? '';

        if ($advert === '') {
            return '';
        }

        if (!str_contains($advert, '[URL]')) {
            return $advert;
        }

        $indent = is_numeric($space)
            ? str_repeat('    ', (int) $space)
            : $space;

        return trim(
            str_replace(
                [PHP_EOL, '[URL]'],
                [PHP_EOL . $indent, getenv('APP_URL') ?: ''],
                $advert
            )
        ) . PHP_EOL;
    }

    private function preloadAds(): void
    {
        if ($this->isCacheValid()) {
            $data = require $this->cacheFile();

            if (is_array($data)) {
                $this->cache = $data;
                return;
            }
        }

        $this->cache = $this->loadAdsFromFiles();
        $this->saveCache();
    }

    private function isCacheValid(): bool
    {
        $cacheFile = $this->cacheFile();

        if (!is_file($cacheFile)) {
            return false;
        }

        $cacheTime = filemtime($cacheFile) ?: 0;

        foreach (glob($this->adsDir() . '/*.txt') ?: [] as $file) {
            if (filemtime($file) > $cacheTime) {
                return false;
            }
        }

        return true;
    }

    private function loadAdsFromFiles(): array
    {
        $ads = [];

        foreach (glob($this->adsDir() . '/*.txt') ?: [] as $file) {
            $ads[basename($file, '.txt')] = file_get_contents($file) ?: '';
        }

        return $ads;
    }

    private function saveCache(): void
    {
        $cacheFile = $this->cacheFile();
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        file_put_contents(
            $cacheFile,
            "<?php return " . var_export($this->cache, true) . ";",
            LOCK_EX
        );
    }

    private function adsDir(): string
    {
        return BASE_DIRECTORY . '/' . self::ADS_DIR;
    }

    private function cacheFile(): string
    {
        return BASE_DIRECTORY . '/' . self::CACHE_FILE;
    }
}
