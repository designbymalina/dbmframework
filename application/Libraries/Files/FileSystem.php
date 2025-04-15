<?php
/**
 * Library: Filesystem
 * A class designed for the DbM Framework and for use in any PHP application.
 *
 * @package Lib\FileSystem
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Lib\Files;

use RuntimeException;

class FileSystem
{
    public function fileMultiDelete($images): ?string
    {
        if (is_array($images)) {
            foreach ($images as $image) {
                if (file_exists($image)) {
                    unlink($image);

                    if (is_file($image)) {
                        return "Something went wrong! The file $image has not been deleted.";
                    }
                } else {
                    return "File $image does not exist!";
                }
            }
        } elseif (file_exists($images)) {
            unlink($images);

            if (is_file($images)) {
                return "Something went wrong! The file $images has not been deleted.";
            }
        } else {
            return "File $images does not exist!";
        }

        return null;
    }

    public function scanDirectory(string $directory, int $sort = 0, array $arraySkip = ['..', '.']): ?array
    {
        if (is_dir($directory)) {
            return array_diff(scandir($directory, $sort), $arraySkip);
        }

        return null;
    }

    public function contentPreview(string $pathFile): ?string
    {
        if (is_file($pathFile) && file_exists($pathFile) && (filesize($pathFile) > 0)) {
            $contentPreview = file_get_contents($pathFile);
            $contentPreview = str_replace([PHP_EOL], ["<br>"], $contentPreview);
        }

        return $contentPreview ?? null;
    }

    public function deleteFile(string $filePath): void
    {
        if (is_file($filePath) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function saveFile(string $filePath, string $fileContent, int $chmod = 0755): void
    {
        $directory = dirname($filePath);
        if (!is_dir($directory) && !mkdir($directory, $chmod, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: $directory");
        }

        if (file_put_contents($filePath, $fileContent) === false) {
            throw new RuntimeException("Unable to write to file: $filePath");
        }

        if (!chmod($filePath, $chmod)) {
            throw new RuntimeException("Failed to set permissions for file: $filePath");
        }
    }

    public function editFile(string $filePath, string $fileContent): void
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("File does not exist: $filePath");
        }

        if (file_put_contents($filePath, $fileContent) === false) {
            throw new RuntimeException("Unable to edit file: $filePath");
        }
    }
}
