<?php
/**
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\Constants;
use App\Service\DbmImageUploadService;
use App\Service\MethodService;
use Dbm\Classes\FrameworkClass;
use Dbm\Classes\TranslationClass;
use DateTime;

/*
 * TODO! Rozbij PanelController na wiecej kontrolerow panelu!
*/
class PanelController extends FrameworkClass
{
    private const DIR_CONTENT = BASE_DIRECTORY . 'data' . DS . 'contents' . DS;
    private const DIR_IMG_PAGE = BASE_DIRECTORY . 'public' . DS . 'images' . DS . 'page' . DS . 'photo' . DS;
    private const DIR_IMG_BLOG = BASE_DIRECTORY . 'public' . DS . 'images' . DS . 'blog' . DS . 'photo' . DS;
    private const DIR_IMG_SECTION = BASE_DIRECTORY . 'public' . DS . 'images' . DS . 'blog' . DS . 'category' . DS . 'photo' . DS;
    private const SPLIT = "<!--@-->";
    private $controllerModel;
    private $translation;

    public function __construct()
    {
        if (!$this->getSession('dbmUserId')) {
            $this->redirect("login");
        }

        $userId = (int) $this->getSession('dbmUserId');

        if ($this->userPermissions($userId) !== 'ADMIN') {
            $this->redirect("index");
        }

        $this->controllerModel = $this->model('panelModel');

        $translation = new TranslationClass();
        $this->translation = $translation;
    }

    public function index()
    {
        $translation = $this->translation;

        $contentFiles = array_diff(scandir(self::DIR_CONTENT), array('..', '.'));

        $allArticles = $this->controllerModel->getAllArticlesLimit(10);

        $arrayArticles = array();

        foreach ($allArticles as $article) {
            $arrayArticles[] = $article->page_header;
        }

        $data = array(
            'meta.title' => $translation->trans('website.name') . ' - Panel Administracyjny',
            'files' => $contentFiles,
            'articles' => $arrayArticles,
        );

        $this->view("panel/admin.html.php", $data);
    }

    public function managePageMethod()
    {
        if ($this->requestData('action') == 'delete') { // TEMP? Look for a better solution!
            $this->setFlash('message' . ucfirst($this->requestData('status')), $this->requestData('message'));
        }

        $contentFiles = array_diff(scandir(self::DIR_CONTENT), array('..', '.'));

        $data = array(
            'meta.title' => 'managePageMethod',
            'files' => $contentFiles,
            'dir' => self::DIR_CONTENT,
        );

        $this->view("panel/manage_page.html.php", $data);
    }

    public function createOrEditPageMethod()
    {
        $file = $this->requestData('file');
        $imageFiles = array_diff(scandir(self::DIR_IMG_PAGE), array('..', '.'));

        if (!empty($file)) {
            $filePath = self::DIR_CONTENT . $file;
            $fileContent = file_get_contents($filePath);
            $fileFields = explode(self::SPLIT, $fileContent);
            $keywords = $fileFields[0];
            $description = $fileFields[1];
            $title = $fileFields[2];
            $content = $fileFields[3];

            $data = [
                'meta.title' => "Page editing - Dashboard DbM Framework",
                'header' => "Editing page",
                'action' => "editPage",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'images' => $imageFiles,
                'file' => $file,
                'fields' => (object) [
                    'keywords' => $keywords,
                    'description' => $description,
                    'title' => $title,
                    'content' => $content,
                ],
            ];
        } else {
            $data = [
                'meta.title' => "Page create - Dashboard DbM Framework",
                'header' => "Create page",
                'action' => "createPage",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'images' => $imageFiles,
                'file' => null,
                'accordion' => true,
            ];
        }

        $this->view("panel/create_edit_page.html.php", $data);
    }

    public function createPageMethod()
    {
        $fileName = $this->requestData('filename');
        $filePath = self::DIR_CONTENT . $fileName . '.txt';

        if (empty($this->requestData('filename'))) {
            $this->setFlash('messageDanger', 'Complete the file name field');
        } elseif (file_exists($filePath)) {
            $this->setFlash('messageWarning', 'A file with the given name already exists. You can edit the content of the page.');
        } else {
            $fileName = $fileName . '.txt';
            $fileContent = $this->requestData('keywords') . "\n" . self::SPLIT . "\n" . $this->requestData('description')
                . "\n" . self::SPLIT . "\n" . $this->requestData('title') . "\n" . self::SPLIT . "\n" . $this->requestData('content');

            $handle = fopen($filePath, 'w');
            fwrite($handle, $fileContent);
            fclose($handle);
            chmod($filePath, 0777);

            $this->setFlash('messageSuccess', 'The new page has been successfully created.');
        }

        $this->redirect("panel/createOrEditPage", ['file' => $fileName]);
    }

    public function editPageMethod()
    {
        $filePath = self::DIR_CONTENT . $this->requestData('file');
        $fileContent = $this->requestData('keywords') . "\n" . self::SPLIT . "\n" . $this->requestData('description')
            . "\n" . self::SPLIT . "\n" . $this->requestData('title') . "\n" . self::SPLIT . "\n" . $this->requestData('content');
        file_put_contents($filePath, $fileContent);

        $this->setFlash('messageSuccess', 'The page has been successfully edited.');
        $this->redirect("panel/createOrEditPage", ['file' => $this->requestData('file')]);
    }

    public function manageBlogMethod()
    {
        if ($this->requestData('action') == 'delete') {
            $this->setFlash('message' . ucfirst($this->requestData('status')), $this->requestData('message'));
        }

        $allArticles = $this->controllerModel->getJoinArticlesFirst();

        $data = array(
            'meta.title' => 'manageBlogMethod',
            'articles' => $allArticles,
        );

        $this->view("panel/manage_blog.html.php", $data);
    }

    public function createOrEditBlogMethod()
    {
        $id = (int) $this->requestData('id');
        $imageFiles = array_diff(scandir(self::DIR_IMG_BLOG), array('..', '.'));
        $allSections = $this->controllerModel->arraySections();
        $allUsers = $this->controllerModel->arrayUsers();
        $dataArticle = $this->controllerModel->getArticle($id);

        if (!empty($id) && ($id !== 0)) {
            $data = [
                'meta.title' => "Article editing - Dashboard DbM Framework",
                'header' => "Editing article",
                'action' => "editBlog",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'id' => $id,
                'images' => $imageFiles,
                'sections' => $allSections,
                'users' => $allUsers,
                'fields' => (object) [
                    'keywords' => $dataArticle->meta_keywords,
                    'description' => $dataArticle->meta_description,
                    'title' => $dataArticle->meta_title,
                    'header' => $dataArticle->page_header,
                    'content' => $dataArticle->page_content,
                    'image' => $dataArticle->image_thumb,
                    'sid' => (int) $dataArticle->section_id,
                    'uid' => (int) $dataArticle->user_id,
                ],
            ];
        } else {
            $data = [
                'meta.title' => "Article create - Dashboard DbM Framework",
                'header' => "Create article",
                'action' => "createBlog",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'id' => $id,
                'images' => $imageFiles,
                'sections' => $allSections,
                'users' => $allUsers,
                'accordion' => true,
            ];
        }

        $this->view("panel/create_edit_blog.html.php", $data);
    }

    public function createBlogMethod()
    {
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $title = $this->requestData('title');
        $header = $this->requestData('header');
        $content = $this->requestData('content');
        $section = $this->requestData('section');
        $user = $this->requestData('user');
        $image = $this->requestData('image');

        $imageFiles = array_diff(scandir(self::DIR_IMG_BLOG), array('..', '.'));
        $allSections = $this->controllerModel->arraySections();
        $allUsers = $this->controllerModel->arrayUsers();

        $data = [
            'meta.title' => "Article create - Dashboard DbM Framework",
            'header' => "Create article",
            'action' => "createBlog",
            'submit' => '<i class="fas fa-plus mr-2"></i>Create',
            'images' => $imageFiles,
            'sections' => $allSections,
            'users' => $allUsers,
            'accordion' => true,
            'fields' => (object) [
                'keywords' => $keywords,
                'description' => $description,
                'title' => $title,
                'header' => $header,
                'content' => $content,
                'image' => $image,
                'sid' => $section,
                'uid' => $user,
            ],
        ];

        $errorValidate = $this->controllerModel->validateFormBlog($keywords, $description, $title, $header, $content, $section, $user);

        if (empty($errorValidate)) {
            $userId = (int) $this->requestData('user');
            $sectionId = (int) $this->requestData('section');
            $lastId = false;

            empty($image) ? $image = null : false;

            $sqlInsert = [':uid' => $userId, 'sid' => $sectionId, ':title' => $title, ':description' => $description,
                ':keywords' => $keywords, ':header' => $header, ':content' => $content, ':thumb' => $image];

            if ($this->controllerModel->insertArticle($sqlInsert)) {
                $lastId = $this->controllerModel->getLastId();
                $this->setFlash('messageSuccess', 'The new article has been successfully created.');
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
            }

            $this->redirect("panel/createOrEditBlog", ['id' => $lastId]);
        } else {
            $data = array_merge($data, $errorValidate);
            $this->view("panel/create_edit_blog.html.php", $data);
        }
    }

    public function editBlogMethod()
    {
        $id = (int) $this->requestData('id');
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $title = $this->requestData('title');
        $header = $this->requestData('header');
        $content = $this->requestData('content');
        $section = $this->requestData('section');
        $user = $this->requestData('user');

        !empty($this->requestData('image')) ? $image = $this->requestData('image') : $image = null;

        $datetime = new DateTime();
        $dateNow = $datetime->format('Y-m-d H:i:s');

        $sqlUpdate = [':uid' => $user, 'sid' => $section, ':title' => $title, ':description' => $description, ':keywords' => $keywords,
            ':header' => $header, ':content' => $content, ':thumb' => $image, ':date' => $dateNow, ':id' => $id];

        if ($this->controllerModel->updateArticle($sqlUpdate)) {
            $this->setFlash('messageSuccess', 'The article has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("panel/createOrEditBlog", ['id' => $id]);
    }

    public function manageBlogSectionsMethod()
    {
        if ($this->requestData('action') == 'delete') {
            $this->setFlash('message' . ucfirst($this->requestData('status')), $this->requestData('message'));
        }

        $querySections = $this->controllerModel->getAllSections();

        $data = array(
            'meta.title' => 'manageBlogSectionsMethod',
            'sections' => $querySections,
        );

        $this->view("panel/manage_blog_sections.html.php", $data);
    }

    public function createOrEditBlogSectionMethod()
    {
        $id = (int) $this->requestData('id');
        $imageFiles = array_diff(scandir(self::DIR_IMG_SECTION), array('..', '.'));
        $dataSection = $this->controllerModel->getSection($id);

        if (!empty($id) && ($id !== 0)) {
            $data = [
                'meta.title' => "Section editing - Dashboard DbM Framework",
                'header' => "Editing section",
                'action' => "editSection",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'id' => $id,
                'images' => $imageFiles,
                'fields' => (object) [
                    'keywords' => $dataSection->section_keywords,
                    'description' => $dataSection->section_description,
                    'name' => $dataSection->section_name,
                    'image' => $dataSection->image_thumb,
                ],
            ];
        } else {
            $data = [
                'meta.title' => "Section create - Dashboard DbM Framework",
                'header' => "Create section",
                'action' => "createSection",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'id' => $id,
                'images' => $imageFiles,
            ];
        }

        $this->view("panel/create_edit_blog_section.html.php", $data);
    }

    public function createSectionMethod()
    {
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $name = $this->requestData('name');
        $image = $this->requestData('image');

        $imageFiles = array_diff(scandir(self::DIR_IMG_SECTION), array('..', '.'));

        $data = [
            'meta.title' => "Section create - Dashboard DbM Framework",
            'header' => "Create section",
            'action' => "createSection",
            'submit' => '<i class="fas fa-plus mr-2"></i>Create',
            'images' => $imageFiles,
            'fields' => (object) [
                'keywords' => $keywords,
                'description' => $description,
                'name' => $name,
                'image' => $image,
            ],
        ];

        $errorValidate = $this->controllerModel->validateFormBlogSection($name, $description, $keywords);

        if (empty($errorValidate)) {
            $lastId = false;

            empty($image) ? $image = null : false;

            $sqlInsert = [':name' => $name, ':description' => $description, ':keywords' => $keywords, ':thumb' => $image];

            if ($this->controllerModel->insertSection($sqlInsert)) {
                $lastId = $this->controllerModel->getLastId();
                $this->setFlash('messageSuccess', 'The new section has been successfully created.');
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
            }

            $this->redirect("panel/createOrEditBlogSection", ['id' => $lastId]);
        } else {
            $data = array_merge($data, $errorValidate);
            $this->view("panel/create_edit_blog_section.html.php", $data);
        }
    }

    public function editSectionMethod()
    {
        $id = (int) $this->requestData('id');
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $name = $this->requestData('name');

        !empty($this->requestData('image')) ? $image = $this->requestData('image') : $image = null;

        $sqlUpdate = [':name' => $name, ':description' => $description, ':keywords' => $keywords, ':thumb' => $image, ':id' => $id];

        if ($this->controllerModel->updateSection($sqlUpdate)) {
            $this->setFlash('messageSuccess', 'The section has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("panel/createOrEditBlogSection", ['id' => $id]);
    }

    public function tabelsMethod()
    {
        $contentFiles = array_diff(scandir(self::DIR_CONTENT), array('..', '.'));

        $data = array(
            'meta.title' => 'managePagesMethod()',
            'files' => $contentFiles,
        );

        $this->view("panel/tabels.html.php", $data);
    }

    public function ajaxUploadImageMethod(): void
    {
        $type = $this->requestData('type');

        ($type === 'blog') ? $pathImage = Constants::PATH_BLOG_IMAGES : $pathImage = Constants::PATH_PAGE_IMAGES;

        if (!empty($_FILES['file'])) {
            $fileName = $_FILES["file"]["name"];
            $fileTempName = $_FILES["file"]["tmp_name"];

            $imageUpload = new DbmImageUploadService();
            $arrayResult = $imageUpload->createImages($fileTempName, $fileName, $pathImage);

            echo json_encode($arrayResult);
        } else {
            echo json_encode(['status' => "danger", 'message' => "Please select an image to upload!"]);
        }
    }

    public function ajaxDeleteImageMethod(): void
    {
        $file = $this->requestData('file');
        $type = $this->requestData('type');

        ($type === 'blog') ? $pathImage = Constants::PATH_BLOG_IMAGES : $pathImage = Constants::PATH_PAGE_IMAGES;

        $pathPhoto = $pathImage . 'photo/' . $file;
        $pathThumb = $pathImage . 'thumb/' . $file;

        $methodService = new MethodService();
        $deleteImages = $methodService->fileMultiDelete([$pathPhoto, $pathThumb]);

        if ($deleteImages !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteImages]);
        } else {
            echo json_encode(['status' => "success", 'message' => "The image has been successfully deleted."]);
        }
    }

    public function ajaxDeleteFileMethod(): void
    {
        $file = $this->requestData('file');
        $pathFile = self::DIR_CONTENT . $file;

        $methodService = new MethodService();
        $deleteFile = $methodService->fileMultiDelete($pathFile);

        if ($deleteFile !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteFile]);
        } else {
            echo json_encode(['status' => "success", 'message' => "The file has been successfully deleted."]);
        }
    }

    public function ajaxDeleteArticleMethod(): void
    {
        $articleId = (int) $this->requestData('id');

        if ($this->controllerModel->deleteArticle($articleId)) {
            echo json_encode(['status' => "success", 'message' => 'The article has been successfully deleted.']);
        } else {
            echo json_encode(['status' => "danger", 'message' => 'An unexpected error occurred!']);
        }
    }

    public function ajaxDeleteSectionMethod(): void
    {
        $sectionId = (int) $this->requestData('id');

        if ($this->controllerModel->deleteSection($sectionId)) {
            echo json_encode(['status' => "success", 'message' => 'The section has been successfully deleted.']);
        } else {
            echo json_encode(['status' => "danger", 'message' => 'An unexpected error occurred!']);
        }
    }

    public function ajaxUploadImageSectionMethod(): void
    {
        if (!empty($_FILES['file'])) {
            $fileName = $_FILES["file"]["name"];
            $fileTempName = $_FILES["file"]["tmp_name"];

            $imageUpload = new DbmImageUploadService();
            $arrayResult = $imageUpload->createImages($fileTempName, $fileName, Constants::PATH_SECTION_IMAGES);

            echo json_encode($arrayResult);
        } else {
            echo json_encode(['status' => "danger", 'message' => "Please select an image to upload!"]);
        }
    }

    public function ajaxDeleteImageSectionMethod(): void
    {
        $file = $this->requestData('file');

        $methodService = new MethodService();
        $deleteImages = $methodService->fileMultiDelete(
            [Constants::PATH_SECTION_IMAGES . 'photo/' . $file, Constants::PATH_SECTION_IMAGES . 'thumb/' . $file]
        );

        if ($deleteImages !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteImages]);
        } else {
            echo json_encode(['status' => "success", 'message' => "The image has been successfully deleted."]);
        }
    }
}
