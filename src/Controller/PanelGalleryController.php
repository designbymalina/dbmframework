<?php
/*
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\ConstantConfig;
use App\Model\PanelGalleryModel;
use App\Service\DbmImageUploadService;
use App\Service\MethodService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;
use DateTime;

class PanelGalleryController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        if (!$this->getSession('dbmUserId')) {
            $this->redirect("./login");
        }

        parent::__construct($database);

        $userId = (int) $this->getSession('dbmUserId');

        if ($this->userPermissions($userId) !== 'ADMIN') {
            $this->redirect("./");
        }

        $model = new PanelGalleryModel($database);
        $this->model = $model;
    }

    public function manageGalleryMethod()
    {
        if ($this->requestData('action') == 'delete') {
            $this->setFlash('message' . ucfirst($this->requestData('status')), $this->requestData('message'));
        }

        $meta = array(
            'meta.title' => 'Manage Gallery - Dashboard DbM Framework',
        );

        $queryGallery = $this->model->getGalleryPhotos();

        $this->render('panel/manage_gallery.phtml', [
            'meta' => $meta,
            'gallery' => $queryGallery,
        ]);
    }

    public function addOrEditPhotoMethod()
    {
        $id = (int) $this->requestData('id');
        $dataPhoto = $this->model->getPhoto($id);

        $fields = [];

        if ($dataPhoto) {
            $fields = (object) [
                'uid' => (int) $dataPhoto->user_id,
                'filename' => $dataPhoto->filename,
                'title' => $dataPhoto->title,
                'description' => $dataPhoto->description,
                'status' => $dataPhoto->status,
            ];
        }

        if (!empty($id) && ($id !== 0)) {
            $meta = [
                'meta.title' => "Gallery editing - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Editing photo",
                'action' => "editPhoto",
                'submit' => '<i class="fas fa-edit mr-2"></i>Edit photo',
                'id' => $id,
            ];
        } else {
            $meta = [
                'meta.title' => "Add photo - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Add photo",
                'action' => "addPhoto",
                'submit' => '<i class="fas fa-upload mr-3"></i>Upload photo',
            ];
        }

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
        $photoStatus = 'Warning';
        $photoMessage = 'An unexpected error in createImages()!';
        $photoName = null;

        $meta = [
            'meta.title' => "Add photo - Dashboard DbM Framework",
        ];

        $page = [
            'header' => "Add photo",
            'action' => "addPhoto",
            'submit' => '<i class="fa fa-plus mr-2"></i>Upload',
        ];

        if (!empty($_FILES['file'])) {
            $fileName = $_FILES["file"]["name"];
            $fileTempName = $_FILES["file"]["tmp_name"];

            if (!empty($fileName)) {
                $imageUpload = new DbmImageUploadService();
                $arrayPhoto = $imageUpload->createImages($fileTempName, $fileName, ConstantConfig::PATH_GALLERY_IMAGES);

                (!empty($arrayPhoto['status'])) ? $photoStatus = $arrayPhoto['status'] : $photoStatus = null;
                (!empty($arrayPhoto['message'])) ? $photoMessage = $arrayPhoto['message'] : $photoMessage = null;
                (!empty($arrayPhoto['data'])) ? $photoName = $arrayPhoto['data'] : $photoName = null;
            }
        }

        $fields = (object) [
            'title' => $title,
            'description' => $description,
        ];

        $errorValidate = $this->model->validateFormGallery($title, $photoStatus, $photoMessage);

        if (empty($errorValidate)) {
            $userId = (int) $this->getSession('dbmUserId');

            $sqlInsert = [':uid' => $userId, ':filename' => $photoName, ':title' => $title, ':description' => $description];

            if ($this->model->insertPhoto($sqlInsert)) {
                $this->setFlash('messageSuccess', 'The photo has been successfully upload.');
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
            }

            $this->redirect("./panel/addOrEditPhoto");
        } else {
            $this->render('panel/add_edit_photo.phtml', [
                'meta' => $meta,
                'page' => $page,
                'fields' => $fields,
                'validate' => !empty($errorValidate) ? $errorValidate : null,
            ]);
        }
    }

    public function editPhotoMethod()
    {
        $id = (int) $this->requestData('id');
        $status = (int) $this->requestData('status');
        $title = $this->requestData('title');
        $description = $this->requestData('description');

        $datetime = new DateTime();
        $dateNow = $datetime->format('Y-m-d H:i:s');

        $sqlUpdate = [':title' => $title, ':description' => $description, ':status' => $status, ':date' => $dateNow, ':id' => $id];

        if ($this->model->updatePhoto($sqlUpdate)) {
            $this->setFlash('messageSuccess', 'The photo has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("./panel/addOrEditPhoto", ['id' => $id]);
    }

    public function ajaxDeletePhotoMethod(): void
    {
        $id = (int) $this->requestData('id');

        $queryPhoto = $this->model->getPhoto($id);
        $file = $queryPhoto->filename;

        $arrayPathFile = [
            ConstantConfig::PATH_GALLERY_IMAGES . 'photo/' . $file,
            ConstantConfig::PATH_GALLERY_IMAGES . 'thumb/' . $file,
        ];

        $methodService = new MethodService();
        $deleteFile = $methodService->fileMultiDelete($arrayPathFile);

        if ($deleteFile !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteFile]);
        } else {
            if ($this->model->deletePhoto($id)) {
                echo json_encode(['status' => "success", 'message' => 'The photo has been successfully deleted.']);
            } else {
                echo json_encode(['status' => "danger", 'message' => 'An unexpected error occurred!']);
            }
        }
    }
}
