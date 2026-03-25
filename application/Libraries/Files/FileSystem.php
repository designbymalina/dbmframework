<?php

/**
 * Library: Filesystem
 * A class designed for the DbM Framework and for use in any PHP application.
 *
 * @package Dbm\Libraries\Files\FileSystem
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Libraries\Files;

use RuntimeException;

/**
 * Class FileSystem
 */
final class FileSystem
{
    private int $defaultDirMode = 0o775;
    private int $defaultFileMode = 0o644;

    /**
     * Sprawdza, czy plik istnieje.
     *
     * @param string $path Ścieżka do pliku.
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Sprawdza, czy podana ścieżka jest plikiem.
     *
     * @param string $path
     * @return bool
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Odczytuje zawartość pliku.
     *
     * @param string $filePath Ścieżka do pliku.
     * @return string|null Zawartość pliku lub null, jeśli nie istnieje lub jest pusty.
     */
    public function readFile(string $filePath): ?string
    {
        if (!is_file($filePath) || filesize($filePath) === 0) {
            return null;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        return $content;
    }

    /**
     * Kopiuje pojedynczy plik.
     *
     * @param string $from Ścieżka do kopiowanego pliku.
     * @param string $to Ścieżka docelowa.
     */
    public function copyFile(string $from, string $to): void
    {
        if (!is_file($from)) {
            throw new RuntimeException("Source file not found: $from");
        }

        $this->ensureDirectory(dirname($to));

        if (!copy($from, $to)) {
            throw new RuntimeException("Failed to copy file: $from → $to");
        }
    }

    /**
     * Zapisuje nowy plik lub nadpisuje istniejący.
     *
     * @param string $filePath Ścieżka do pliku.
     * @param string $fileContent Treść do zapisania.
     * @param int $flags Tryb zapisu (np. LOCK_EX).
     * @throws RuntimeException Jeśli nie można utworzyć katalogu, zapisać lub ustawić uprawnień.
     */
    public function saveFile(
        string $filePath,
        string $fileContent,
        int $flags = LOCK_EX
    ): void {
        $this->ensureDirectory(dirname($filePath));

        if (file_put_contents($filePath, $fileContent, $flags) === false) {
            throw new RuntimeException("Unable to write to file: $filePath");
        }

        @chmod($filePath, $this->defaultFileMode);
    }

    /**
     * Edytuje istniejący plik.
     *
     * @param string $filePath Ścieżka do pliku.
     * @param string $fileContent Nowa zawartość pliku.
     * @throws RuntimeException Jeśli plik nie istnieje lub nie można zapisać.
     */
    public function editFile(string $filePath, string $fileContent): void
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("File does not exist: $filePath");
        }

        if (file_put_contents($filePath, $fileContent) === false) {
            throw new RuntimeException("Unable to edit file: $filePath");
        }
    }

    /**
     * Usuwa pojedynczy plik.
     *
     * @param string $filePath Ścieżka do pliku.
     */
    public function deleteFile(string $filePath): void
    {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * [?] Zmienia nazwę pliku lub katalogu.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     */
    public function renameFile(string $sourcePath, string $destinationPath): void
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException("File does not exist: $sourcePath");
        }

        if (!rename($sourcePath, $destinationPath)) {
            throw new RuntimeException("Failed to rename $sourcePath to $destinationPath");
        }
    }

    /**
     * Usuwa wiele plików (lub jeden) i zwraca komunikat o błędzie, jeśli coś pójdzie nie tak.
     *
     * @param string|array<int, string> $images Ścieżka lub tablica ścieżek do plików.
     * @return string|null Komunikat błędu lub null, jeśli wszystko OK.
     */
    public function fileMultiDelete(mixed $images): ?string
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

    /**
     * Odczytuje zawartość pliku za pomocą strumienia (fopen/fread).
     * Użyteczne, gdy chcesz kontrolować tryb odczytu (np. binarny).
     *
     * @param string $filePath Ścieżka do pliku.
     * @param string $mode Tryb odczytu (np. 'r', 'rb', 'r+') TODO! append -> mode 'a', etc.
     * @return string|null Zawartość pliku lub null, jeśli nie istnieje lub jest pusty.
     */
    public function readFileStream(string $filePath, string $mode = 'r'): ?string
    {
        if (!is_file($filePath)) {
            return null;
        }

        $size = filesize($filePath);

        if ($size === false || $size === 0) {
            return null;
        }

        $handle = fopen($filePath, $mode);

        if ($handle === false) {
            throw new RuntimeException("Unable to open file for reading: $filePath");
        }

        if (!flock($handle, LOCK_SH)) {
            fclose($handle);
            throw new RuntimeException("Unable to lock file for reading: $filePath");
        }

        $content = fread($handle, $size);

        flock($handle, LOCK_UN);
        fclose($handle);

        return $content !== false ? $content : null;
    }

    /**
     * Zapisuje zawartość do pliku z blokadą zapisu.
     * Gwarantuje, że tylko jeden proces zapisuje w danym momencie.
     *
     * @param string $filePath Ścieżka do pliku
     * @param string $content Zawartość
     * @param string $mode Tryb odczytu (np. 'w', 'wb')
     */
    public function writeFileStream(string $filePath, string $content, string $mode = 'w'): void
    {
        $this->ensureDirectory(dirname($filePath));

        $handle = fopen($filePath, $mode);
        if ($handle === false) {
            throw new RuntimeException("Unable to open file for writing: $filePath");
        }

        // Blokada wyłączna (tylko jeden zapis)
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new RuntimeException("Unable to lock file for writing: $filePath");
        }

        $bytes = fwrite($handle, $content);
        if ($bytes === false) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw new RuntimeException("Unable to write to file: $filePath");
        }

        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        @chmod($filePath, $this->defaultFileMode);
    }

    /**
     * Zwraca listę plików katalogu (pomija katalogi).
     *
     * @param string $directory
     * @return array<int, string>
     */
    public function listFiles(string $directory, ?string $extension = null): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);

        if ($files === false) {
            return [];
        }

        $result = [];

        foreach ($files as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $this->normalizePath($directory . '/' . $item);

            if (!is_file($path)) {
                continue;
            }

            if ($extension !== null) {
                if (pathinfo($item, PATHINFO_EXTENSION) !== ltrim($extension, '.')) {
                    continue;
                }
            }

            $result[] = $path;
        }

        return $result;
    }

    /**
     * Zwraca listę plików w katalogu rekursywnie (tylko pliki).
     *
     * @return array<int, string>
     */
    public function listFilesRecursively(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Sprawdza, czy podana ścieżka jest katalogiem.
     *
     * @param string $path
     * @return bool
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Zwraca listę plików w katalogu, z pominięciem określonych elementów.
     *
     * @param string $directory
     * @param 0|1|2 $sort
     * @param array<int, string> $arraySkip
     * @return array<int, string>|null
     */
    public function scanDir(string $directory, int $sort = SCANDIR_SORT_ASCENDING, array $arraySkip = ['..', '.']): ?array
    {
        if (!is_dir($directory)) {
            return null;
        }

        $files = scandir($directory, $sort);

        if ($files === false) {
            return null;
        }

        /** @var array<int, string> $filtered */
        $filtered = array_values(array_diff($files, $arraySkip));

        return $filtered;
    }

    /**
     * Kopiuje katalog rekursywnie.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public function copyDir(string $from, string $to): void
    {
        if (!is_dir($from)) {
            return;
        }

        $this->ensureDirectory($to);

        $files = scandir($from);

        if ($files === false) {
            return;
        }

        foreach ($files as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $src = $from . '/' . $item;
            $dst = $to . '/' . $item;

            if (is_dir($src)) {
                $this->copyDir($src, $dst);
            } else {
                copy($src, $dst);
            }
        }
    }

    /**
     * Usuwa katalog rekursywnie.
     *
     * @param string $path
     * @return void
     */
    public function deleteDir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = scandir($path);

        if ($files === false) {
            return;
        }

        foreach ($files as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path . '/' . $item;
            is_dir($full) ? $this->deleteDir($full) : unlink($full);
        }

        rmdir($path);
    }

    /**
     * Usuwa katalog jeśli jest pusty.
     *
     * @param string $path
     * @return void
     */
    public function deleteDirIfEmpty(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = scandir($path);

        if ($files === false) {
            return;
        }

        if (count($files) === 2) {
            rmdir($path);
        }
    }

    /**
     * Zwraca listę katalogów w katalogu (tylko katalogi, bez rekursji)
     *
     * @param string $directory
     * @return array<int, string>
     */
    public function listDirs(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);

        if ($files === false) {
            return [];
        }

        $dirs = [];

        foreach ($files as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                $dirs[] = $path;
            }
        }

        return $dirs;
    }

    /**
     * Przenosi przesłany plik do docelowej lokalizacji.
     *
     * INFO! Poprawne i bezpieczne - pod warunkiem, że: metoda jest używana wyłącznie do uploadu HTTP
     * oraz $tmpPath pochodzi z $_FILES
     *
     * @param string $tmp Ścieżka tymczasowa (z $_FILES).
     * @param string $target Ścieżka docelowa.
     * @throws RuntimeException Jeśli plik nie jest przesłany lub nie można go przenieść.
     * @return void
     */
    public function moveUploadedFile(string $tmp, string $target): void
    {
        if (!is_uploaded_file($tmp)) {
            throw new RuntimeException('Not an uploaded file.');
        }

        $this->ensureDirectory(dirname($target));

        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Unable to move uploaded file.');
        }
    }

    /**
     * Tworzy katalog, jeśli nie istnieje i ustawia uprawnienia.
     *
     * @param string $path Ścieżka do katalogu.
     */
    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            if (!@mkdir($path, $this->defaultDirMode, true) && !is_dir($path)) {
                throw new RuntimeException("Cannot create directory: {$path}");
            }
        }
    }

    /**
     * Normalizuje ścieżkę do formatu zgodnego z systemem operacyjnym.
     *
     * @param string $path Ścieżka do normalizacji.
     * @return string Normalizowana ścieżka.
     */
    public function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * Zwraca zawartość pliku w formacie HTML (zamienia nowe linie na <br>).
     *
     * @param string $pathFile Ścieżka do pliku.
     * @return string|null Zawartość pliku jako HTML lub null.
     */
    public function contentPreview(string $pathFile): ?string
    {
        if (!is_file($pathFile) || filesize($pathFile) <= 0) {
            return null;
        }

        $content = file_get_contents($pathFile);

        if ($content === false) {
            return null;
        }

        return str_replace(PHP_EOL, '<br />', $content);
    }
}
