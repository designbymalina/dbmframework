<?php
/*
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\PanelPageService;
use App\Utility\MethodsUtility;
use Dbm\Classes\AdminBaseController;
use Dbm\Interfaces\DatabaseInterface;

class PanelPageController extends AdminBaseController
{
    private const DIR_CONTENT = BASE_DIRECTORY . 'data/content/';

    private $utility;
    private $service;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->utility = new MethodsUtility();
        $this->service = new PanelPageService($this->utility);
    }

    public function managePageMethod()
    {
        $contentFiles = $this->utility->scanDirectory(self::DIR_CONTENT);

        $this->render('panel/manage_page.phtml', [
            'meta' => ['meta.title' => 'managePageMethod'],
            'files' => $contentFiles,
            'dir' => self::DIR_CONTENT,
        ]);
    }

    public function createOrEditPageMethod()
    {
        $file = $this->requestData('file');
        $pageData = $this->service->preparePageData($file);

        $this->render('panel/create_edit_page.phtml', [
            'meta' => $pageData['meta'],
            'page' => $pageData['page'],
            'fields' => $pageData['fields'],
        ]);
    }

    public function createPageMethod()
    {
        $fileName = $this->requestData('filename');
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $title = $this->requestData('title');
        $content = $this->requestData('content');

        if (empty($fileName)) {
            $this->setFlash('messageDanger', 'Complete the file name field');
        } else {
            $result = $this->service->createPage($fileName, $keywords, $description, $title, $content);

            if ($result['status'] === 'danger') {
                $this->setFlash('messageDanger', $result['message']);
            } elseif ($result['status'] === 'success') {
                $this->setFlash('messageSuccess', $result['message']);
            }

            $this->redirect("./panel/createOrEditPage", ['file' => $result['fileName'] ?? $fileName]);
        }
    }

    public function editPageMethod()
    {
        $file = $this->requestData('file');
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $title = $this->requestData('title');
        $content = $this->requestData('content');

        $result = $this->service->editPage($file, $keywords, $description, $title, $content);

        if ($result) {
            $this->setFlash('messageSuccess', 'The page has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'Failed to edit the page.');
        }

        $this->redirect("./panel/createOrEditPage", ['file' => $file]);
    }

    public function ajaxDeleteFileMethod(): void
    {
        $file = $this->requestData('file');
        $pathFile = self::DIR_CONTENT . $file;

        $deleteFile = $this->utility->fileMultiDelete($pathFile);

        if ($deleteFile !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteFile]);
        } else {
            $this->setFlash('messageSuccess', 'The file has been successfully deleted.');
            echo json_encode(['status' => "success", 'message' => "The file has been successfully deleted."]);
        }
    }
}
