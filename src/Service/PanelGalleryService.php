<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use App\Config\ConstantConfig;
use App\Form\PanelGalleryForm;
use App\Model\PanelGalleryModel;
use App\Utility\ErrorLoggerUtility;
use App\Utility\MethodsUtility;
use App\Utility\ResizeUploadImageUtility;
use Dbm\Interfaces\DatabaseInterface;
use DateTime;
use Exception;

class PanelGalleryService
{
    private $model;
    private $form;
    private $logger;

    public function __construct(DatabaseInterface $database)
    {
        $this->model = new PanelGalleryModel($database);
        $this->form = new PanelGalleryForm();
        $this->logger = new ErrorLoggerUtility();
    }

    public function getGalleryPhotos()
    {
        return $this->model->getGalleryPhotos();
    }

    public function getPhoto(int $id)
    {
        return $this->model->getPhoto($id);
    }

    public function prepareAddOrEditPhotoData(int $id): array
    {
        $photo = $this->getPhoto($id);

        // Meta information for the page
        $meta = ['meta.title' => $id ? 'Edit Photo - Dashboard DbM Framework' : 'Add New Photo - Dashboard DbM Framework'];

        // Define $page based on whether we're editing or adding a photo
        if (!empty($id) && ($id !== 0)) {
            $page = [
                'header' => "Editing photo",
                'action' => "editPhoto",
                'submit' => '<i class="fas fa-edit mr-2"></i>Edit photo',
                'id' => $id,
            ];
        } else {
            $page = [
                'header' => "Add photo",
                'action' => "addPhoto",
                'submit' => '<i class="fas fa-upload mr-3"></i>Upload photo',
            ];
        }

        // Fields for the form
        $fields = $photo ? (object)[
            'uid' => (int) $photo->user_id,
            'filename' => $photo->filename,
            'title' => $photo->title,
            'description' => $photo->description,
            'status' => $photo->status,
        ] : null;

        return [$meta, $page, $fields];
    }

    public function prepareAddPhotoData($file, string $title, string $description): array
    {
        // Ustawienie meta, page i fields
        $meta = ['meta.title' => "Add photo - Dashboard DbM Framework"];
        $page = [
            'header' => "Add photo",
            'action' => "addPhoto",
            'submit' => '<i class="fa fa-plus mr-2"></i>Upload',
        ];

        // Przetwarzanie przesłanego zdjęcia
        $uploadResult = $this->processPhotoUpload($file);

        // Walidacja formularza
        $errorValidate = $this->validateFormGallery($title, $uploadResult['status'], $uploadResult['message']);

        // Przygotowanie pól formularza
        $fields = (object) [
            'title' => $title,
            'description' => $description,
        ];

        return [$meta, $page, $fields, $uploadResult, $errorValidate];
    }

    public function addInsertPhoto(int $userId, ?string $photoName, string $title, string $description): bool
    {
        $sqlInsert = [':uid' => $userId, ':filename' => $photoName, ':title' => $title, ':description' => $description];

        return $this->model->insertPhoto($sqlInsert);
    }

    public function editUpdatePhoto(int $id, string $title, string $description, int $status): bool
    {
        $datetime = new DateTime();
        $dateNow = $datetime->format('Y-m-d H:i:s');

        $sqlUpdate = [
            ':title' => $title,
            ':description' => $description,
            ':status' => $status,
            ':date' => $dateNow,
            ':id' => $id
        ];

        return $this->model->updatePhoto($sqlUpdate);
    }

    public function deletePhoto(int $id): array
    {
        $photo = $this->getPhoto($id);
        $file = $photo->filename;

        $filePaths = [
            ConstantConfig::PATH_GALLERY_IMAGES . 'photo/' . $file,
            ConstantConfig::PATH_GALLERY_IMAGES . 'thumb/' . $file,
        ];

        $methodUtility = new MethodsUtility();
        $deleteFile = $methodUtility->fileMultiDelete($filePaths);

        if ($deleteFile !== null) {
            return ['status' => 'danger', 'message' => $deleteFile];
        }

        if ($this->model->deletePhoto($id)) {
            return ['status' => 'success', 'message' => 'The photo has been successfully deleted.'];
        }

        return ['status' => 'danger', 'message' => 'An unexpected error occurred!'];
    }

    private function processPhotoUpload($file): array
    {
        if (empty($file) || !isset($file["name"], $file["tmp_name"])) {
            return ['status' => 'danger', 'message' => 'Unexpected error! File not uploaded.'];
        }

        $fileName = $file["name"];
        $fileTempName = $file["tmp_name"];

        if (empty($fileName)) {
            return ['status' => 'danger', 'message' => 'The photo field is required!'];
        }

        try {
            $imageUpload = new ResizeUploadImageUtility();
            $arrayPhoto = $imageUpload->createImages($fileTempName, $fileName, ConstantConfig::PATH_GALLERY_IMAGES);

            return $arrayPhoto; // return ['status' => 'danger or success', 'message' => 'Result message.', 'data' => 'filename-new.jpg'];
        } catch (Exception $e) {
            $this->logger->logException($e);
            return ['status' => 'danger', 'message' => 'Error while processing the image: ' . $e->getMessage()];
        }
    }

    private function validateFormGallery(string $title, ?string $photoStatus, ?string $photoMessage): ?array
    {
        return $this->form->validatePanelGalleryForm($title, $photoStatus, $photoMessage);
    }
}
