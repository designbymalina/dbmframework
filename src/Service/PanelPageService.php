<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use App\Utility\MethodsUtility;
use RuntimeException;

class PanelPageService
{
    private const DIR_CONTENT = BASE_DIRECTORY . 'data/content/';
    private const DIR_IMG_PAGE = BASE_DIRECTORY . 'public/images/page/photo/';
    private const SPLIT = "<!--@-->";

    private $utility;

    public function __construct(MethodsUtility $utility)
    {
        $this->utility = $utility;
    }

    public function getFileFields(string $file): ?array
    {
        $filePath = self::DIR_CONTENT . $file;

        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $fileFields = explode(self::SPLIT, $fileContent);

            return [
                'keywords' => trim($fileFields[0]),
                'description' => trim($fileFields[1]),
                'title' => trim($fileFields[2]),
                'content' => trim($fileFields[3]),
            ];
        }

        return null;
    }

    public function preparePageData(?string $file): array
    {
        $imageFiles = $this->utility->scanDirectory(self::DIR_IMG_PAGE);

        if (!empty($file)) {
            $fileFields = $this->getFileFields($file);

            $meta = [
                'meta.title' => "Page editing - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Editing page",
                'action' => "editPage",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'images' => $imageFiles,
                'file' => $file,
            ];

            $fields = (object) $fileFields;
        } else {
            $meta = [
                'meta.title' => "Page create - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Create page",
                'action' => "createPage",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'images' => $imageFiles,
                'file' => null,
                'accordion' => true,
            ];

            $fields = null;
        }

        return [
            'meta' => $meta,
            'page' => $page,
            'fields' => $fields,
        ];
    }

    public function createPage(string $fileName, string $keywords, string $description, string $title, string $content): array
    {
        $filePath = self::DIR_CONTENT . $fileName . '.txt';

        if (file_exists($filePath)) {
            return [
                'status' => 'danger',
                'message' => 'A file with the given name already exists. You can edit the content of the page.',
            ];
        }

        $fileContent = $keywords . "\n" . self::SPLIT . "\n" . $description . "\n" . self::SPLIT . "\n" . $title . "\n" . self::SPLIT . "\n" . $content;

        $this->saveFile($filePath, $fileContent);

        return [
            'status' => 'success',
            'message' => 'The new page has been successfully created.',
            'fileName' => $fileName . '.txt',
        ];
    }

    public function editPage(string $file, string $keywords, string $description, string $title, string $content): bool
    {
        $filePath = self::DIR_CONTENT . $file;

        $fileContent = $keywords . "\n" . self::SPLIT . "\n" . $description . "\n"
            . self::SPLIT . "\n" . $title . "\n" . self::SPLIT . "\n" . $content;

        if (!file_exists($filePath)) {
            throw new RuntimeException("File does not exist: $filePath");
        }

        if (file_put_contents($filePath, $fileContent) !== false) {
            return true;
        }

        return false;
    }

    private function saveFile($filePath, $fileContent)
    {
        if (!$handle = fopen($filePath, 'w')) {
            throw new RuntimeException("Unable to open file for writing: $filePath");
        }

        fwrite($handle, $fileContent);
        fclose($handle);

        chmod($filePath, 0755);
    }
}
