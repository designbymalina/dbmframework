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

/**
 * Class FileSystem
 */
class FileSystem
{
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
     * Odczytuje zawartość pliku.
     *
     * @param string $filePath Ścieżka do pliku.
     * @return string|null Zawartość pliku lub null, jeśli nie istnieje lub jest pusty.
     */
    public function readFile(string $filePath): ?string
    {
        if (!is_file($filePath) || !file_exists($filePath) || filesize($filePath) === 0) {
            return null;
        }

        return file_get_contents($filePath);
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

        $dir = dirname($to);
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        if (!copy($from, $to)) {
            throw new RuntimeException("Failed to copy file: $from → $to");
        }
    }

    /**
     * Zapisuje nowy plik lub nadpisuje istniejący.
     *
     * @param string $filePath Ścieżka do pliku.
     * @param string $fileContent Treść do zapisania.
     * @param int $chmod Uprawnienia dla pliku i katalogu.
     * @throws RuntimeException Jeśli nie można utworzyć katalogu, zapisać lub ustawić uprawnień.
     */
    public function saveFile(string $filePath, string $fileContent, int $chmod = 0o755): void
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
        if (is_file($filePath) && file_exists($filePath)) {
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
     * @param string|array $images Ścieżka lub tablica ścieżek do plików.
     * @return string|null Komunikat błędu lub null, jeśli wszystko OK.
     */
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
        if (!is_file($filePath) || filesize($filePath) === 0) {
            return null;
        }

        $handle = fopen($filePath, $mode);

        if ($handle === false) {
            throw new RuntimeException("Unable to open file for reading: $filePath");
        }

        // Blokada współdzielona (tylko odczyt)
        if (!flock($handle, LOCK_SH)) {
            fclose($handle);
            throw new RuntimeException("Unable to lock file for reading: $filePath");
        }

        $content = fread($handle, filesize($filePath));
        flock($handle, LOCK_UN);
        fclose($handle);

        return $content !== false ? $content : null;
    }

    /**
     * Zapisuje zawartość do pliku z blokadą zapisu.
     * Gwarantuje, że tylko jeden proces zapisuje w danym momencie.
     *
     * @param string $filePath Ścieżka do pliku.
     * @param string $content Zawartość
     * @param string $mode Tryb odczytu (np. 'w', 'wb').
     * @param int $chmod Uprawnienia
     */
    public function writeFileStream(string $filePath, string $content, string $mode = 'w', int $chmod = 0o644): void
    {
        $directory = dirname($filePath);
        if (!is_dir($directory) && !mkdir($directory, 0o755, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: $directory");
        }

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

        chmod($filePath, $chmod);
    }

    /**
     * Zwraca listę plików katalogu (pomija katalogi).
     *
     * @param string $directory
     * @return array
     */
    public function listFiles(string $directory, ?string $extension = null): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $result = [];

        foreach (scandir($directory) as $item) {
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
     * @param string $directory Ścieżka do katalogu.
     * @param int $sort Sortowanie (0 = rosnąco, 1 = malejąco).
     * @param array $arraySkip Pliki/katalogi do pominięcia.
     * @return array|null Lista plików lub null, jeśli katalog nie istnieje.
     */
    public function scanDir(string $directory, int $sort = 0, array $arraySkip = ['..', '.']): ?array
    {
        if (is_dir($directory)) {
            return array_diff(scandir($directory, $sort), $arraySkip);
        }

        return null;
    }

    /**
     * Tworzy nowy katalog.
     *
     * @param string $path
     * @param int $mode
     * @return void
     */
    public function ensureDir(string $path, int $mode = 0o777): void
    {
        if (!is_dir($path)) {
            mkdir($path, $mode, true);
        }
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

        if (!is_dir($to)) {
            mkdir($to, 0o755, true);
        }

        foreach (scandir($from) as $item) {
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

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path . '/' . $item;
            is_dir($full) ? $this->deleteDir($full) : unlink($full);
        }

        rmdir($path);
    }

    /**
     * Zwraca listę katalogów w katalogu (tylko katalogi, bez rekursji)
     *
     * @param string $directory
     * @return array
     */
    public function listDirs(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $dirs = [];

        foreach (scandir($directory) as $item) {
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

        $this->ensureDir(dirname($target));

        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Unable to move uploaded file.');
        }
    }

    /**
     * Zwraca zawartość pliku w formacie HTML (zamienia nowe linie na <br>).
     *
     * @param string $pathFile Ścieżka do pliku.
     * @return string|null Zawartość pliku jako HTML lub null.
     */
    public function contentPreview(string $pathFile): ?string
    {
        if (is_file($pathFile) && file_exists($pathFile) && (filesize($pathFile) > 0)) {
            $contentPreview = file_get_contents($pathFile);
            $contentPreview = str_replace([PHP_EOL], ["<br>"], $contentPreview);
        }

        return $contentPreview ?? null;
    }

    public function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
