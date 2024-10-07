<?php
/*
 * DbM Framework, class ResizeUploadImage()
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Author: Design by Malina
 *
 * Info: Class need extension PHP GD.
 */

declare(strict_types=1);

namespace App\Utility;

use Exception;

class ResizeUploadImageUtility
{
    private const DIR_ORIGINAL = 'original/';
    private const DIR_PHOTO = 'photo/';
    private const DIR_THUMB = 'thumb/';
    private const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "gif"];
    
    private int $maxLength = 30;
    private int $minWidth = 960;
    // You can change these parameters when loading the class.
    private int $maxFileSize = 6291456; // 6MB (1MB = 1048576 in bytes)
    private int $photoMaxWidth = 1280; // size from 960 to 1920
    private int $thumbMaxWidth = 480; // size from 320 to 640
    private int $imageQuality = 80; // quality from 50 to 100
    
    /**
     * Handles file upload, resizing the image and creating a thumbnail.
     *
     * @param array $file - the uploaded file from $_FILES['file']
     * @param string $imagePath - the directory to store images
     * @return array - status, message and if success data="filename.ext" of the result 
     */
    public function createImages(array $file, string $imagePath = 'images/'): array
    {
        if (!isset($file['tmp_name'], $file['name'], $file['size'])) {
            return ['status' => 'danger', 'message' => 'Invalid file upload.'];
        }

        $fileTempName = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];

        if ($fileSize > $this->maxFileSize) {
            return ['status' => 'danger', 'message' => sprintf('File exceeds the maximum allowed size of %d MB.', $this->maxFileSize / (1024 * 1024))];
        }

        $imageName = $this->transliteratorSanitizeFilename(pathinfo($fileName, PATHINFO_FILENAME));
        $imageExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($imageExt, self::ALLOWED_EXTENSIONS)) {
            return ['status' => 'danger', 'message' => 'Allowed extensions are: .' . implode(', .', self::ALLOWED_EXTENSIONS)];
        }

        $newFilename = $imageName.'_'.uniqid().'.'.$imageExt;
        $targetOriginal = $imagePath.self::DIR_ORIGINAL.$newFilename;
        $targetPhoto = $imagePath.self::DIR_PHOTO.$newFilename;
        $targetThumb = $imagePath.self::DIR_THUMB.$newFilename;

        try {
            if (!empty($errorCheck = $this->checkFolders($imagePath))) {
                return $errorCheck;
            }

            if (strlen($imageName) > $this->maxLength) {
                throw new Exception(sprintf("The photo name is too long, maximum %s characters!", $this->maxLength));
            }

            if (file_exists($targetPhoto)) {
                throw new Exception("The file already exists! Please rename the file and try again.");
            }

            if (move_uploaded_file($fileTempName, $targetOriginal)) {
                $originalDimensions = getimagesize($targetOriginal);
                $width = $originalDimensions[0];
                $height = $originalDimensions[1];

                if ($width < $this->minWidth) {
                    unlink($targetOriginal);
                    throw new Exception(sprintf("The uploaded file is too small. Minimum width is %s pixels.", $this->minWidth));
                }

                // Resize photo
                $this->resizeImage($targetOriginal, $targetPhoto, $imageExt, $width, $height, $this->photoMaxWidth);
                // Resize thumbnail
                $this->resizeImage($targetOriginal, $targetThumb, $imageExt, $width, $height, $this->thumbMaxWidth, false);

                unlink($targetOriginal);

                return [
                    'status' => 'success',
                    'message' => "Image uploaded successfully.",
                    'data' => $newFilename,
                ];
            } else {
                throw new Exception("Image upload failed! Try again.");
            }
        } catch (Exception $e) {
            return [
                'status' => 'danger',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function setPhotoMaxWidth(int $maxWidth): void
    {
        if ($maxWidth >= 960 && $maxWidth <= 1920) {
            $this->photoMaxWidth = $maxWidth;
        }
    }

    public function setThumbMaxWidth(int $maxWidth): void
    {
        if ($maxWidth >= 320 && $maxWidth <= 640) {
            $this->thumbMaxWidth = $maxWidth;
        }
    }

    public function setImageQuality(int $quality): void
    {
        if ($quality >= 50 && $quality <= 100) {
            $this->imageQuality = $quality;
        }
    }

    public function setMaxFileSize(int $size): void
    {
        $this->maxFileSize = $size;
    }

    private function resizeImage(string $sourcePath, string $targetPath, string $extension, int $width, int $height, int $maxWidth, bool $highQuality = true): void
    {
        $aspectRatio = $width / $height;
        $newWidth = $maxWidth;
        $newHeight = (int) ($maxWidth / $aspectRatio);

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($sourcePath);
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new Exception("Unsupported image format.");
        }

        if ($highQuality) {
            imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        } else {
            imagecopyresized($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        }

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($resizedImage, $targetPath, $this->imageQuality);
                break;
            case 'png':
                imagepng($resizedImage, $targetPath);
                break;
            case 'gif':
                imagegif($resizedImage, $targetPath);
                break;
        }

        imagedestroy($resizedImage);
        imagedestroy($sourceImage);
    }

    private function checkFolders(string $basePath): array
    {
        foreach ([self::DIR_ORIGINAL, self::DIR_PHOTO, self::DIR_THUMB] as $dir) {
            if (!file_exists($basePath.$dir)) {
                if (!mkdir($basePath.$dir, 0777, true)) {
                    return [
                        'status' => 'danger',
                        'message' => "ERROR! Unable to create folder {$basePath}{$dir}.",
                    ];
                }
            }
        }
        return [];
    }

    private function transliteratorSanitizeFilename(string $fileName): string
    {
        $safeFilename = iconv('UTF-8', 'ASCII//TRANSLIT', $fileName);
        return strtolower(preg_replace('/[^a-z0-9-]+/', '-', $safeFilename));
    }
}
