<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 *
 * DbmImageUploadService{}
 * Author: Arthur Malinowsky (Design by Malina)
 */

declare(strict_types=1);

namespace App\Utility;

class ResizeUploadImageUtility
{
    public const DIR_ORIGINAL = 'original/';
    public const DIR_PHOTO = 'photo/';
    public const DIR_THUMB = 'thumb/';
    public const ALLOWED_EXTENSIONS = ["jpg", "jpeg"];

    /**
     * Create photo and thumbnail resized
     *
     * @param string $fileTempName
     * @param string $fileName
     * @param string $imagePath
     * @param int $photoMaxWidth
     * @param int $thumbMaxWidth
     * @param int $minWidth
     * @param int $maxLength
     *
     * @return array
     */
    public function createImages(
        string $fileTempName,
        string $fileName,
        string $imagePath = 'images/',
        int $photoMaxWidth = 1280,
        int $thumbMaxWidth = 480,
        int $minWidth = 960,
        int $maxLength = 30
    ): array {
        $imageName = pathinfo($fileName, PATHINFO_FILENAME);
        $imageName = str_replace([' ','_'], '-', preg_replace('/\s+/', '-', $imageName));
        $imageExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $imageExt = str_replace('jpeg', 'jpg', $imageExt);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9-] remove; Lower()', $imageName);
        $newFilename = $safeFilename.'_'.uniqid().'.'.$imageExt;
        $targetOriginal = $imagePath.self::DIR_ORIGINAL.$newFilename;
        $targetPhoto = $imagePath.self::DIR_PHOTO.$newFilename;
        $targetThumb = $imagePath.self::DIR_THUMB.$newFilename;

        $checkFolders = $this->checkFolders($imagePath.self::DIR_ORIGINAL, $imagePath.self::DIR_PHOTO, $imagePath.self::DIR_THUMB);

        if (!empty($checkFolders)) {
            return $checkFolders;
        }

        if (strlen($imageName) > $maxLength) {
            $resMessage = array(
                'status' => "danger",
                'message' => sprintf("The photo name is too long, maximum %s characters!", $maxLength),
            );
        } elseif (!in_array($imageExt, self::ALLOWED_EXTENSIONS)) {
            $resMessage = array(
                "status" => "danger",
                "message" => "File extensions allowed are: ." . implode(' .', self::ALLOWED_EXTENSIONS),
            );
        } elseif (file_exists($targetPhoto)) {
            $resMessage = array(
                'status' => "danger",
                'message' => "The file already exists! An exception occurred, please rename the file and try again.",
            );
        } else {
            // File upload
            if (move_uploaded_file($fileTempName, $targetOriginal)) {
                $originalDimensions = getimagesize($targetOriginal);
                $width = $originalDimensions[0];
                $height = $originalDimensions[1];

                if ($width < $minWidth) {
                    unlink($targetOriginal);

                    $resMessage = array(
                        'status' => "danger",
                        'message' => sprintf("The uploaded file is too small. The minimum width of the image is %s pixels.", $minWidth),
                    );
                } else {
                    // Create photo imagecopyresampled() = higher quality and smaller size
                    if ($width < $photoMaxWidth) {
                        $photoMaxWidth = $width;
                    }

                    $ratio = $width / $height;
                    $aspect = $width / $photoMaxWidth;

                    if ($ratio < 1) {
                        $newWidth = (int) ceil($photoMaxWidth * $ratio);
                        $newHeight = $photoMaxWidth;
                    } else {
                        $newWidth = $photoMaxWidth;
                        $newHeight = (int) ceil($height / $aspect);
                    }

                    $photo = imagecreatetruecolor($newWidth, $newHeight);
                    $source = imagecreatefromjpeg($targetOriginal);
                    imagecopyresampled($photo, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($photo, $targetPhoto, 80); // default optimal quality 75%

                    // Create thumbnails imagecopyresized() = lower quality
                    if ($width < $thumbMaxWidth) {
                        $thumbMaxWidth = $width;
                    }

                    $ratio = $width / $height;
                    $aspect = $width / $thumbMaxWidth;

                    if ($ratio < 1) {
                        $newWidth = (int) ceil($thumbMaxWidth * $ratio);
                        $newHeight = $thumbMaxWidth;
                    } else {
                        $newWidth = $thumbMaxWidth;
                        $newHeight = (int) ceil($height / $aspect);
                    }

                    $thumb = imagecreatetruecolor($newWidth, $newHeight);
                    $source = imagecreatefromjpeg($targetOriginal);
                    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($thumb, $targetThumb);

                    // Delete original image
                    unlink($targetOriginal);

                    // Result
                    $resMessage = array(
                        'status' => "success",
                        'message' => "The image has been successfully uploaded.",
                        'data' => $newFilename,
                    );
                }
            } else {
                $resMessage = array(
                    'status' => "danger",
                    'message' => "The image has not been uploaded! Try again.",
                );
            }
        }

        return $resMessage;
    }

    private function checkFolders(string $dirOriginal, string $dirPhoto, string $dirThumb): array
    {
        $result = array();

        if (!file_exists($dirOriginal)) {
            $result = array(
                'status' => "danger",
                'message' => "ERROR! Folder name and the path: $dirOriginal is invalid. Report the error to the administration.",
            );
        } elseif (!file_exists($dirPhoto)) {
            $result = array(
                'status' => "danger",
                'message' => "ERROR! Folder name and the path: $dirPhoto is invalid. Report the error to the administration.",
            );
        } elseif (!file_exists($dirThumb)) {
            $result = array(
                'status' => "danger",
                'message' => "ERROR! Folder name and the path: $dirThumb is invalid. Report the error to the administration.",
            );
        }

        return $result;
    }
}
