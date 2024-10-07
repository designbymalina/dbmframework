<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Utility\ResizeUploadImageUtility;

class ResizeUploadImageUtilityTest extends TestCase
{
    private ResizeUploadImageUtility $imageUtility;
    private string $imagePath;
    private string $imagePathTest;

    protected function setUp(): void
    {
        $this->imagePath = dirname(__DIR__) . DS . 'Resources' . DS . 'images' . DS;
        $this->imagePathTest = dirname(__DIR__) . DS . 'Resources' . DS . 'images_test' . DS;

        if (!file_exists($this->imagePath)) {
            mkdir($this->imagePath, 0777, true);
        }

        if (!file_exists($this->imagePathTest)) {
            mkdir($this->imagePathTest, 0777, true);
        }

        $this->imageUtility = new ResizeUploadImageUtility();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->imagePathTest)) {
            $this->deleteDirectory($this->imagePathTest);
        }
    }

    /* INFO: Aby przetestowac w ResizeUploadImageUtility() zmien move_uploaded_file() na copy()
    public function testImageUploadSuccess(): void
    {
        // Mock plik do przesyłania
        $file = [
            'tmp_name' => $this->imagePath . 'mock-image.jpg',
            'name' => 'image-1.jpg',
            'size' => 500000,
        ];

        // Sprawdzenie poprawnego przesłania pliku
        $result = $this->imageUtility->createImages($file, $this->imagePathTest);

        // Sprawdzanie poprawności przesłania
        $this->assertEquals('success', $result['status'], $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertFileExists($this->imagePathTest . 'photo/' . $result['data']);
        $this->assertFileExists($this->imagePathTest . 'thumb/' . $result['data']);
    }

    public function testImageWidthTooSmall(): void
    {
        // Mock plik do przesyłania
        $file = [
            'tmp_name' => $this->imagePath . '/mock-image-small.jpg',
            'name' => 'image-small-1.jpg',
            'size' => 500000,
        ];

        // Ustawimy minimalną szerokość obrazu na 960px
        $result = $this->imageUtility->createImages($file, $this->imagePathTest);

        $this->assertEquals('danger', $result['status']);
        $this->assertStringContainsString('The uploaded file is too small', $result['message']);
    } */

    public function testMaxFileSizeExceeded(): void
    {
        // Ustawiamy maksymalny rozmiar pliku na 1MB
        $this->imageUtility->setMaxFileSize(1048576); // 1MB

        // Mock plik do przesyłania (rozmiar 2MB)
        $file = [
            'tmp_name' => $this->imagePath . 'mock-image-large.png',
            'name' => 'image-large-1.png',
            'size' => 2097152,
        ];

        $result = $this->imageUtility->createImages($file, $this->imagePathTest);

        $this->assertEquals('danger', $result['status']);
        $this->assertStringContainsString('File exceeds the maximum allowed size', $result['message']);
    }

    public function testUnsupportedFileFormat(): void
    {
        // Mock plik z nieobsługiwanym rozszerzeniem
        $file = [
            'tmp_name' => $this->imagePath . 'mock-image-unsupported.bmp',
            'name' => 'unsupported-1.bmp',
            'size' => 500000,
        ];

        $result = $this->imageUtility->createImages($file, $this->imagePathTest);

        $this->assertEquals('danger', $result['status']);
        $this->assertStringContainsString('Allowed extensions are', $result['message']);
    }

    public function testNoFileUploaded(): void
    {
        // Symulacja braku pliku
        $file = [];

        $result = $this->imageUtility->createImages($file, $this->imagePathTest);

        $this->assertEquals('danger', $result['status']);
        $this->assertStringContainsString('Invalid file upload', $result['message']);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }

        rmdir($dir);
    }
}
