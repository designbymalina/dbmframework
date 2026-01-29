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

namespace Dbm\Content\Flatfile;

use Dbm\Content\Flatfile\Contracts\DataFlatfileInterface;
use Dbm\Content\Flatfile\Dto\FileOperationDto;
use Dbm\Exceptions\NotFoundException;
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Localization\LanguageService;

class DataFlatfile implements DataFlatfileInterface
{
    private string $contentBasePath;

    public function __construct(
        private Request $request,
        private Logger $logger,
        private LanguageService $languageService,
        string $contentBasePath = BASE_DIRECTORY . '/data/content/'
    ) {
        $this->contentBasePath = rtrim($contentBasePath, '/') . '/';
    }

    public function dataFlatFile(
        string $type,
        string $slug,
        string|int|null $space = null,
        ?string $path = null
    ): FileOperationDto {
        $arrKeys = ['keywords', 'description', 'title', 'content'];

        if ($path === null) {
            $path = $this->contentBasePath;
        }

        $file = $this->buildFileName($slug);
        $path = $path . $file;

        if (!is_file($path) || !is_readable($path)) {
            throw new NotFoundException("Page '{$slug}' not found! Check the file '{$file}'.");
        }

        try {
            $txtHtml = trim(file_get_contents($path));
            $arrayData = $this->arrayFillKeys(
                $arrKeys,
                explode('<!--@-->', $txtHtml)
            );

            if (empty($arrayData[$type])) {
                throw new NotFoundException(
                    "Missing '{$type}' in {$file}"
                );
            }

            $data = $type === 'content'
                ? $this->replaceContent($arrayData[$type], $space)
                : strip_tags(trim($arrayData[$type]));

            return new FileOperationDto(true, $data, null);

        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['file' => $path, 'type' => $type]);

            throw $e instanceof NotFoundException
                ? $e : new NotFoundException('Error processing content file', 0, $e);
        }
    }

    public function buildFileName(string $slug, string $ext = 'txt'): string
    {
        $lang = strtolower((string) $this->languageService->detectLanguage());

        $prefix = $this->resolvePrefix($slug);

        $name = $prefix !== '' ? "{$prefix}-{$slug}" : $slug;

        return "{$lang}_{$name}." . $ext;
    }

    private function resolvePrefix(string $slug): string
    {
        $baseFolderName = basename(BASE_DIRECTORY);
        $fullPath = $this->request->getUri()->getPath();

        if (($pos = strpos($fullPath, $baseFolderName)) !== false) {
            $track = ltrim(substr($fullPath, $pos + strlen($baseFolderName)), '/');
        } else {
            $track = ltrim($fullPath, '/');
        }

        $suffix = '/' . $slug;
        if (str_ends_with($track, $suffix)) {
            $prefix = substr($track, 0, -strlen($suffix));
            return str_replace('/', '-', trim($prefix, '/'));
        }

        return '';
    }

    private function arrayFillKeys(array $arrayKeys, array $arrayValues): ?array
    {
        if (!is_array($arrayKeys)) {
            return null;
        }

        $arrayFilled = [];

        foreach ($arrayKeys as $key => $value) {
            if (array_key_exists($key, $arrayValues)) {
                $arrayFilled[$value] = $arrayValues[$key];
            }
        }

        return $arrayFilled;
    }

    private function replaceContent(string $content, string|int|null $space = null): string
    {
        if ($space === null || $space === '') {
            $indent = '';
        } elseif (is_int($space) || ctype_digit((string) $space)) {
            $indent = str_repeat('    ', (int) $space);
        } else {
            $indent = (string) $space;
        }

        return trim(
            str_replace(
                ["\r\n", "\r", "\n", "[URL]"],
                ["\n", "\n", "\n" . $indent, getenv('APP_URL')],
                $content
            )
        ) . "\n";
    }

    // Method not used
    private function getAvailablePages(): array
    {
        $lang = strtolower((string) $this->languageService->detectLanguage());
        $path = $this->contentBasePath;
        $pattern = $path . "{$lang}_*.txt";
        $files = glob($pattern) ?: [];

        return array_reduce($files, function ($pages, $file) use ($lang) {
            $slug = str_replace("{$lang}_", '', basename($file, ".txt"));
            $pages[$slug] = ucwords(str_replace('-', ' ', $slug));
            return $pages;
        }, []);
    }
}
