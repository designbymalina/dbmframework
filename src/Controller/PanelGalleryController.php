<?php
/*
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\PanelGalleryService;
use Dbm\Classes\AdminBaseController;
use Dbm\Interfaces\DatabaseInterface;

class PanelGalleryController extends AdminBaseController
{
    private $galleryService;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->galleryService = new PanelGalleryService($database);
    }

    public function manageGalleryMethod()
    {
        if ($this->requestData('action') == 'delete') {
            $this->setFlash('message' . ucfirst($this->requestData('status')), $this->requestData('message'));
        }

        $meta = ['meta.title' => 'Manage Gallery - Dashboard DbM Framework'];
        $gallery = $this->galleryService->getGalleryPhotos();

        $this->render('panel/manage_gallery.phtml', [
            'meta' => $meta,
            'gallery' => $gallery,
        ]);
    }

    public function addOrEditPhotoMethod()
    {
        $id = (int) $this->requestData('id');

        [$meta, $page, $fields] = $this->galleryService->prepareAddOrEditPhotoData($id);

        $this->render('panel/add_edit_photo.phtml', [
            'meta' => $meta,
            'page' => $page,
            'fields' => !empty($fields) ? $fields : null,
        ]);
    }

    public function addPhotoMethod()
    {
        $title = $this->requestData('title');
        $description = $this->requestData('description');

        [$meta, $page, $fields, $uploadResult, $errorValidate] = $this->galleryService->prepareAddPhotoData($_FILES['file'] ?? null, $title, $description);

        if (!empty($errorValidate)) {
            $this->render('panel/add_edit_photo.phtml', [
                'meta' => $meta,
                'page' => $page,
                'fields' => $fields,
                'validate' => $errorValidate,
            ]);

            return;
        }

        if (empty($uploadResult['data'])) {
            $this->setFlash('messageDanger', 'Photo upload failed. No data available.');
            $this->redirect("./panel/addOrEditPhoto");

            return;
        }

        $userId = (int) $this->getSession(getenv('APP_SESSION_KEY'));

        if ($this->galleryService->addInsertPhoto($userId, $uploadResult['data'], $title, $description)) {
            $this->setFlash('messageSuccess', 'The photo has been successfully uploaded.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred while saving the photo.');
        }

        $this->redirect("./panel/addOrEditPhoto");
    }

    public function editPhotoMethod()
    {
        $id = (int) $this->requestData('id');
        $title = $this->requestData('title');
        $description = $this->requestData('description');
        $status = $this->requestData('status');

        if ($this->galleryService->editUpdatePhoto($id, $title, $description, $status)) {
            $this->setFlash('messageSuccess', 'The photo has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("./panel/addOrEditPhoto", ['id' => $id]);
    }

    public function ajaxDeletePhotoMethod(): void
    {
        $id = (int) $this->requestData('id');
        $result = $this->galleryService->deletePhoto($id);

        echo json_encode($result);
    }
}
