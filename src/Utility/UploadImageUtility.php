<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Utility;

class UploadImageUtility
{
    private string $targetDir = "uploads/";
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private int $maxFileSize = 2097152;
    private bool $renameIfExist = false;
    private ?int $maxWidth = null;
    private ?int $maxHeight = null;
    private array $errors = [];

    public function setTargetDir(string $targetDir): void
    {
        if ($this->validateDirectory($targetDir)) {
            $this->targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
    }

    public function setAllowedTypes(array $allowedTypes): void
    {
        if ($this->validateAllowedTypes($allowedTypes)) {
            $this->allowedTypes = $allowedTypes;
        }
    }

    public function setMaxFileSize(int $maxFileSize): void
    {
        if ($maxFileSize > 0) {
            $this->maxFileSize = $maxFileSize;
        } else {
            $this->errors[] = "Błąd konfiguracji rozmiaru pliku.";
        }
    }

    public function setMaxWidth(?int $maxWidth): void
    {
        if ($maxWidth === null || $maxWidth > 0) {
            $this->maxWidth = $maxWidth;
        } else {
            $this->errors[] = "Błąd konfiguracji szerokość obrazu.";
        }
    }

    public function setMaxHeight(?int $maxHeight): void
    {
        if ($maxHeight === null || $maxHeight > 0) {
            $this->maxHeight = $maxHeight;
        } else {
            $this->errors[] = "Błąd konfiguracji wysokość obrazu.";
        }
    }

    public function setRenameIfExist(bool $rename): void
    {
        $this->renameIfExist = $rename;
    }

    public function uploadImage(array $file)
    {
        if (!empty($this->errors)) {
            return $this->getErrorsAsString();
        }

        if (!$this->validateDirectory($this->targetDir)) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "Błąd podczas przesyłania pliku.";
            return false;
        }

        if (!$this->validateFileType($file['type']) || !$this->validateFileSize($file['size'])) {
            return false;
        }

        if (!$this->validateImageDimensions($file['tmp_name'])) {
            return false;
        }

        $fileName = basename($file['name']);
        $targetFilePath = $this->targetDir . $fileName;

        if (file_exists($targetFilePath)) {
            if ($this->renameIfExist) {
                $fileName = $this->generateUniqueFileName($fileName);
                $targetFilePath = $this->targetDir . $fileName;
            } else {
                $this->errors[] = "Plik o tej nazwie już istnieje.";
                return false;
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $this->errors[] = "Błąd podczas zapisywania pliku.";
            return false;
        }

        return $fileName;
    }

    public function deleteImage(string $fileName): string
    {
        $filePath = $this->targetDir . $fileName;

        if (is_file($filePath) && file_exists($filePath)) {
            unlink($filePath);

            return "Plik $fileName został usunięty.";
        } else {
            return "Plik nie istnieje.";
        }
    }

    public function getErrorsAsString(): string
    {
        return implode("<br>", $this->errors);
    }

    private function validateDirectory(string $directory): bool
    {
        if (is_string($directory) && !empty($directory)) {
            if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
                $this->errors[] = "Nie udało się utworzyć katalogu docelowego: $directory";
                return false;
            }
            return true;
        }

        $this->errors[] = "Nieprawidłowy katalog docelowy.";
        return false;
    }

    private function validateAllowedTypes(array $allowedTypes): bool
    {
        $valid = array_reduce($allowedTypes, fn ($carry, $type) => $carry && is_string($type), true);

        if (!$valid) {
            $this->errors[] = "Nieprawidłowe typy plików.";
            return false;
        }

        return true;
    }

    private function validateFileType(string $type): bool
    {
        if (!in_array($type, $this->allowedTypes)) {
            $allowedTypes = str_replace(['image/', 'jpeg'], ['.', 'jpg'], $this->allowedTypes);
            $this->errors[] = "Nieprawidłowy typ pliku. Dozwolone rozszerzenia " . implode(', ', $allowedTypes) . '.';
            return false;
        }

        return true;
    }

    private function validateFileSize(int $size): bool
    {
        if ($size > $this->maxFileSize) {
            $this->errors[] = "Plik jest za duży. Dozwolony rozmiar " . $this->filesizeCounter($this->maxFileSize, 0) . '.';
            return false;
        }

        return true;
    }

    private function validateImageDimensions(string $filePath): bool
    {
        $imageInfo = getimagesize($filePath);

        if ($imageInfo === false) {
            $this->errors[] = "Przesłany plik nie jest obrazem.";
            return false;
        }

        if ($this->maxWidth && $imageInfo[0] > $this->maxWidth) {
            $this->errors[] = "Szerokość obrazu przekracza dozwolony wymiar " . $this->maxWidth . ' pikseli.';
            return false;
        }

        if ($this->maxHeight && $imageInfo[1] > $this->maxHeight) {
            $this->errors[] = "Wysokość obrazu przekracza dozwolony wymiar " . $this->maxHeight . ' pikseli.';
            return false;
        }

        return true;
    }

    private function generateUniqueFileName(string $fileName): string
    {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME);

        if (file_exists($this->targetDir . $fileName)) {
            return $fileBaseName . '_' . uniqid() . '.' . $fileExtension;
        }

        return $fileName;
    }

    private function filesizeCounter(int $bytes, int $decimals = 2): string
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen((string)$bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
}
