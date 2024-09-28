<?php
/*
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\PanelToolsService;
use App\Utility\MethodsUtility;
use Dbm\Classes\AdminBaseController;
use Dbm\Interfaces\DatabaseInterface;

class PanelToolsController extends AdminBaseController
{
    private $utility;
    private $service;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->utility = new MethodsUtility();
        $this->service = new PanelToolsService();
    }

    public function toolsLogsMethod()
    {
        $type = $this->requestData('type');
        $action = $this->requestData('action');
        $file = $this->requestData('file');
        $fileDir = $this->service->toolsPath($type);
        $filePath = $fileDir . $file;

        if ($action == 'delete') {
            $this->utility->deleteFile($filePath);
            $file = null;
        }

        list($title, $link) = $this->service->getTitleAndLink($type);

        $contentFiles = $this->utility->scanDirectory($fileDir, 1, ['..', '.', 'mailer', 'logger']);
        $contentPreview = $this->utility->contentPreview($filePath);

        $this->render('panel/tools_logs.phtml', [
            'meta' => ['meta.title' => $title],
            'files' => $contentFiles,
            'preview' => $contentPreview,
            'title' => $title,
            'link' => $link,
            'type' => $type,
            'item' => $file,
        ]);
    }
}
