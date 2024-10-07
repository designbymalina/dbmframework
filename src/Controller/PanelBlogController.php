<?php
/*
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\ConstantConfig;
use App\Form\PanelBlogForm;
use App\Model\PanelBlogModel;
use App\Service\PanelBlogService;
use App\Utility\MethodsUtility;
use App\Utility\ResizeUploadImageUtility;
use Dbm\Classes\AdminBaseController;
use Dbm\Interfaces\DatabaseInterface;

class PanelBlogController extends AdminBaseController
{
    private const DIR_IMG_BLOG = BASE_DIRECTORY . 'public/images/blog/photo/';
    private const DIR_IMG_SECTION = BASE_DIRECTORY . 'public/images/blog/category/photo/';

    private $model;
    private $service;
    private $form;
    private $utility;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->model = new PanelBlogModel($database);
        $this->service = new PanelBlogService($database);
        $this->form = new PanelBlogForm();
        $this->utility = new MethodsUtility();
    }

    public function manageBlogMethod()
    {
        $allArticles = $this->model->getJoinArticlesFirst();

        $this->render('panel/manage_blog.phtml', [
            'meta' => [ 'meta.title' => 'manageBlogMethod'],
            'articles' => $allArticles,
        ]);
    }

    public function createOrEditBlogMethod()
    {
        $id = (int) $this->requestData('id');

        $dataArticle = $this->model->getArticle($id);
        $dirImages = $this->utility->scanDirectory(self::DIR_IMG_BLOG);
        $meta = $this->service->prepareMeta($id);
        $page = $this->service->preparePageData($id);
        $fields = $this->service->prepareFields($dataArticle);
        $allSections = $this->service->getFormattedSections();
        $allUsers = $this->service->getFormattedUsers();

        $this->render('panel/create_edit_blog.phtml', [
            'meta' => $meta,
            'page' => $page,
            'fields' => $fields,
            'images' => $dirImages,
            'sections' => $allSections,
            'users' => $allUsers,
        ]);
    }

    public function createBlogMethod()
    {
        $formData = $this->service->getArticleFormData($this);
        $allSections = $this->service->getFormattedSections();
        $allUsers = $this->service->getFormattedUsers();
        $dirImages = $this->utility->scanDirectory(self::DIR_IMG_BLOG);
        $errorValidate = $this->service->makeValidateArticleForm($formData);

        if (empty($errorValidate)) {
            $insertParams = $this->service->prepareInsertArticle($formData);
            $lastId = $this->service->makeInsertArticle($insertParams);

            if ($lastId) {
                $this->setFlash('messageSuccess', 'The new article has been successfully created.');
                $this->redirect("./panel/createOrEditBlog", ['id' => $lastId]);
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
                $this->redirect("./panel/createOrEditBlog");
            }
        } else {
            $this->render('panel/create_edit_blog.phtml', [
                'meta' => $this->service->prepareMetaArticle(),
                'page' => $this->service->preparePageArticle(),
                'fields' => $this->service->prepareFieldsArticle($formData),
                'sections' => $allSections,
                'users' => $allUsers,
                'images' => $dirImages,
                'validate' => !empty($errorValidate) ? $errorValidate : null,
            ]);
        }
    }

    public function editBlogMethod()
    {
        $formData = $this->service->getArticleFormData($this);
        $updateParams = $this->service->prepareUpdateArticle($formData);

        if ($this->model->updateArticle($updateParams)) {
            $this->setFlash('messageSuccess', 'The article has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("./panel/createOrEditBlog", ['id' => $formData['id']]);
    }

    public function manageBlogSectionsMethod()
    {
        $querySections = $this->model->getAllSections();

        $this->render('panel/manage_blog_sections.phtml', [
            'meta' => ['meta.title' => 'manageBlogSectionsMethod'],
            'sections' => $querySections,
        ]);
    }

    public function createOrEditBlogSectionMethod()
    {
        $id = (int) $this->requestData('id');
        $dataSection = $this->model->getSection($id);
        $dirImages = $this->utility->scanDirectory(self::DIR_IMG_SECTION);
        $meta = $this->service->prepareMetaSection($id);
        $page = $this->service->preparePageSection($id);

        $fields = $this->service->prepareFieldsSection($dataSection);

        $this->render('panel/create_edit_blog_section.phtml', [
            'meta' => $meta,
            'page' => $page,
            'fields' => $fields,
            'images' => $dirImages,
        ]);
    }

    public function createSectionMethod()
    {
        $formData = $this->service->getSectionFormData($this);

        $dirImages = $this->utility->scanDirectory(self::DIR_IMG_SECTION);
        $errorValidate = $this->form->validateFormBlogSection($formData);

        if (empty($errorValidate)) {
            $lastId = $this->service->createSection($formData);

            if ($lastId) {
                $this->setFlash('messageSuccess', 'The new section has been successfully created.');
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
            }

            $this->redirect("./panel/createOrEditBlogSection", ['id' => $lastId]);
        } else {
            $this->render('panel/create_edit_blog_section.phtml', [
                'meta' => ['meta.title' => "Section create - Dashboard DbM Framework"],
                'page' => ['header' => "Create section", 'action' => "createSection", 'submit' => '<i class="fas fa-plus mr-2"></i>Create'],
                'fields' => $this->service->prepareFieldsSection($formData),
                'images' => $dirImages,
                'validate' => !empty($errorValidate) ? $errorValidate : null,
            ]);
        }
    }

    public function editSectionMethod()
    {
        $formData = $this->service->getSectionFormData($this);

        $isUpdated = $this->service->updateSection($formData);

        if ($isUpdated) {
            $this->setFlash('messageSuccess', 'The section has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("./panel/createOrEditBlogSection", ['id' => $formData['id']]);
    }

    public function ajaxDeleteArticleMethod(): void
    {
        $articleId = (int) $this->requestData('id');

        if ($this->model->deleteArticle($articleId)) {
            $this->setFlash('messageSuccess', 'The article has been successfully deleted.');
            echo json_encode(['status' => "success", 'message' => 'The article has been successfully deleted.']);
        } else {
            echo json_encode(['status' => "danger", 'message' => 'An unexpected error occurred!']);
        }
    }

    public function ajaxDeleteSectionMethod(): void
    {
        $sectionId = (int) $this->requestData('id');

        if ($this->model->deleteSection($sectionId)) {
            $this->setFlash('messageSuccess', 'The section has been successfully deleted.');
            echo json_encode(['status' => "success", 'message' => 'The section has been successfully deleted.']);
        } else {
            echo json_encode(['status' => "danger", 'message' => 'An unexpected error occurred!']);
        }
    }

    public function ajaxUploadImageMethod(): void
    {
        $type = $this->requestData('type');
        $pathImage = $this->service->getTypeModule($type);

        if (!empty($_FILES['file'])) {
            $imageUpload = new ResizeUploadImageUtility();
            $arrayResult = $imageUpload->createImages($_FILES['file'], $pathImage);

            echo json_encode($arrayResult);
        } else {
            echo json_encode(['status' => "danger", 'message' => "Please select an image to upload!"]);
        }
    }

    public function ajaxDeleteImageMethod(): void
    {
        $file = $this->requestData('file');
        $type = $this->requestData('type');

        $pathImage = $this->service->getTypeModule($type);

        $pathPhoto = $pathImage . 'photo/' . $file;
        $pathThumb = $pathImage . 'thumb/' . $file;

        $methodUtility = new MethodsUtility();
        $deleteImages = $methodUtility->fileMultiDelete([$pathPhoto, $pathThumb]);

        if ($deleteImages !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteImages]);
        } else {
            echo json_encode(['status' => "success", 'message' => "The image has been successfully deleted."]);
        }
    }
}
