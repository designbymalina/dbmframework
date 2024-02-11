<?php
/**
 * DbM Framework (PHP MVC Simple CMS)
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\ConstantConfig;
use App\Model\PanelModel;
use App\Service\DbmImageUploadService;
use App\Service\MethodService;
use Dbm\Classes\Translation;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;
use DateTime;

/*
 * TODO! Rozbij PanelController na wiecej kontrolerow panelu!
*/
class PanelController extends BaseController
{
    private const DIR_CONTENT = BASE_DIRECTORY . 'data/content/';
    private const DIR_IMG_PAGE = BASE_DIRECTORY . 'public/images/page/photo/';
    private const DIR_IMG_BLOG = BASE_DIRECTORY . 'public/images/blog/photo/';
    private const DIR_IMG_SECTION = BASE_DIRECTORY . 'public/images/blog/category/photo/';
    private const SPLIT = "<!--@-->";

    private $model;
    private $translation;

    public function __construct(DatabaseInterface $database)
    {
        if (!$this->getSession('dbmUserId')) {
            $this->redirect("login");
        }

        parent::__construct($database);

        $userId = (int) $this->getSession('dbmUserId');

        if ($this->userPermissions($userId) !== 'ADMIN') {
            $this->redirect("index");
        }

        $model = new PanelModel($database);
        $this->model = $model;

        $translation = new Translation();
        $this->translation = $translation;
    }

    public function index()
    {
        $translation = $this->translation;

        $contentFiles = array_diff(scandir(self::DIR_CONTENT), array('..', '.'));

        $allArticles = $this->model->getAllArticlesLimit(10);

        $arrayArticles = array();

        foreach ($allArticles as $article) {
            $arrayArticles[] = $article->page_header;
        }

        $meta = array(
            'meta.title' => $translation->trans('website.name') . ' - Panel Administracyjny',
        );

        $this->render('panel/admin.phtml', [
            'meta' => $meta,
            'files' => $contentFiles,
            'articles' => $arrayArticles,
        ]);
    }

    public function managePageMethod()
    {
        if ($this->requestData('action') == 'delete') { // TEMP? Look for a better solution!
            $this->setFlash('message' . ucfirst($this->requestData('status')), $this->requestData('message'));
        }

        $contentFiles = array_diff(scandir(self::DIR_CONTENT), array('..', '.'));

        $meta = array(
            'meta.title' => 'managePageMethod',
        );

        $this->render('panel/manage_page.phtml', [
            'meta' => $meta,
            'files' => $contentFiles,
            'dir' => self::DIR_CONTENT,
        ]);
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

            $fields = (object) [
                'keywords' => $keywords,
                'description' => $description,
                'title' => $title,
                'content' => $content,
            ];
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
        }

        $this->render('panel/create_edit_page.phtml', [
            'meta' => $meta,
            'page' => $page,
            'fields' => !empty($fields) ? $fields : null,
        ]);
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

        $allArticles = $this->model->getJoinArticlesFirst();

        $meta = array(
            'meta.title' => 'manageBlogMethod',
        );

        $this->render('panel/manage_blog.phtml', [
            'meta' => $meta,
            'articles' => $allArticles,
        ]);
    }

    public function createOrEditBlogMethod()
    {
        $id = (int) $this->requestData('id');
        $imageFiles = array_diff(scandir(self::DIR_IMG_BLOG), array('..', '.'));
        $allSections = $this->model->arraySections();
        $allUsers = $this->model->arrayUsers();
        $dataArticle = $this->model->getArticle($id);

        $fields = [];

        if ($dataArticle) {
            $fields = (object) [
                'keywords' => $dataArticle->meta_keywords,
                'description' => $dataArticle->meta_description,
                'title' => $dataArticle->meta_title,
                'header' => $dataArticle->page_header,
                'content' => $dataArticle->page_content,
                'image' => $dataArticle->image_thumb,
                'sid' => (int) $dataArticle->section_id,
                'uid' => (int) $dataArticle->user_id,
            ];
        }

        if (!empty($id) && ($id !== 0)) {
            $meta = [
                'meta.title' => "Article editing - Dashboard DbM Framework",
            ];

            $page = [
                'meta.title' => "Article editing - Dashboard DbM Framework",
                'header' => "Editing article",
                'action' => "editBlog",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'id' => $id,
            ];
        } else {
            $meta = [
                'meta.title' => "Article create - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Create article",
                'action' => "createBlog",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'id' => $id,
                'accordion' => true,
            ];
        }

        $this->render('panel/create_edit_blog.phtml', [
            'meta' => $meta,
            'page' => $page,
            'fields' => !empty($fields) ? $fields : null,
            'images' => $imageFiles,
            'sections' => $allSections,
            'users' => $allUsers,
        ]);
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
        $allSections = $this->model->arraySections();
        $allUsers = $this->model->arrayUsers();

        $meta = [
            'meta.title' => "Article create - Dashboard DbM Framework",
        ];

        $page = [
            'header' => "Create article",
            'action' => "createBlog",
            'submit' => '<i class="fas fa-plus mr-2"></i>Create',
            'accordion' => true,
        ];

        $fields = (object) [
            'keywords' => $keywords,
            'description' => $description,
            'title' => $title,
            'header' => $header,
            'content' => $content,
            'image' => $image,
            'sid' => $section,
            'uid' => $user,
        ];

        $errorValidate = $this->model->validateFormBlog($keywords, $description, $title, $header, $content, $section, $user);

        if (empty($errorValidate)) {
            $userId = (int) $this->requestData('user');
            $sectionId = (int) $this->requestData('section');
            $lastId = false;

            empty($image) ? $image = null : false;

            $sqlInsert = [':uid' => $userId, 'sid' => $sectionId, ':title' => $title, ':description' => $description,
                ':keywords' => $keywords, ':header' => $header, ':content' => $content, ':thumb' => $image];

            if ($this->model->insertArticle($sqlInsert)) {
                $lastId = $this->model->getLastId();
                $this->setFlash('messageSuccess', 'The new article has been successfully created.');
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
            }

            $this->redirect("panel/createOrEditBlog", ['id' => $lastId]);
        } else {
            $this->render('panel/create_edit_blog.phtml', [
                'meta' => $meta,
                'page' => $page,
                'fields' => $fields,
                'images' => $imageFiles,
                'sections' => $allSections,
                'users' => $allUsers,
                'validate' => !empty($errorValidate) ? $errorValidate : null,
            ]);
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

        if ($this->model->updateArticle($sqlUpdate)) {
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

        $querySections = $this->model->getAllSections();

        $meta = array(
            'meta.title' => 'manageBlogSectionsMethod',
        );

        $this->render('panel/manage_blog_sections.phtml', [
            'meta' => $meta,
            'sections' => $querySections,
        ]);
    }

    public function createOrEditBlogSectionMethod()
    {
        $id = (int) $this->requestData('id');
        $imageFiles = array_diff(scandir(self::DIR_IMG_SECTION), array('..', '.'));
        $dataSection = $this->model->getSection($id);

        $fields = [];

        if ($dataSection) {
            $fields = (object) [
                'keywords' => $dataSection->section_keywords,
                'description' => $dataSection->section_description,
                'name' => $dataSection->section_name,
                'image' => $dataSection->image_thumb,
            ];
        }

        if (!empty($id) && ($id !== 0)) {
            $meta = [
                'meta.title' => "Section editing - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Editing section",
                'action' => "editSection",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'id' => $id,
            ];
        } else {
            $meta = [
                'meta.title' => "Section create - Dashboard DbM Framework",
            ];

            $page = [
                'header' => "Create section",
                'action' => "createSection",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'id' => $id,
            ];
        }

        $this->render('panel/create_edit_blog_section.phtml', [
            'meta' => $meta,
            'page' => $page,
            'images' => $imageFiles,
            'fields' => !empty($fields) ? $fields : null,
        ]);
    }

    public function createSectionMethod()
    {
        $keywords = $this->requestData('keywords');
        $description = $this->requestData('description');
        $name = $this->requestData('name');
        $image = $this->requestData('image');

        $imageFiles = array_diff(scandir(self::DIR_IMG_SECTION), array('..', '.'));

        $objectFields = (object) [
            'keywords' => $keywords,
            'description' => $description,
            'name' => $name,
            'image' => $image,
        ];

        $errorValidate = $this->model->validateFormBlogSection($name, $description, $keywords);

        if (empty($errorValidate)) {
            $lastId = false;

            empty($image) ? $image = null : false;

            $sqlInsert = [':name' => $name, ':description' => $description, ':keywords' => $keywords, ':thumb' => $image];

            if ($this->model->insertSection($sqlInsert)) {
                $lastId = $this->model->getLastId();
                $this->setFlash('messageSuccess', 'The new section has been successfully created.');
            } else {
                $this->setFlash('messageDanger', 'An unexpected error occurred!');
            }

            $this->redirect("panel/createOrEditBlogSection", ['id' => $lastId]);
        } else {
            $this->render('panel/create_edit_blog_section.phtml', [
                'meta' => [
                    'meta.title' => "Section create - Dashboard DbM Framework",
                ],
                'page' => [
                    'header' => "Create section",
                    'action' => "createSection",
                    'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                ],
                'images' => $imageFiles,
                'fields' => $objectFields,
                'validate' => !empty($errorValidate) ? $errorValidate : null,
            ]);
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

        if ($this->model->updateSection($sqlUpdate)) {
            $this->setFlash('messageSuccess', 'The section has been successfully edited.');
        } else {
            $this->setFlash('messageDanger', 'An unexpected error occurred!');
        }

        $this->redirect("panel/createOrEditBlogSection", ['id' => $id]);
    }

    public function tabelsMethod()
    {
        $contentFiles = array_diff(scandir(self::DIR_CONTENT), array('..', '.'));

        $meta = array(
            'meta.title' => 'managePagesMethod()',
        );

        $this->render('panel/tabels.phtml', [
            'meta' => $meta,
            'files' => $contentFiles,
        ]);
    }

    public function ajaxUploadImageMethod(): void
    {
        $type = $this->requestData('type');

        ($type === 'blog') ? $pathImage = ConstantConfig::PATH_BLOG_IMAGES : $pathImage = ConstantConfig::PATH_PAGE_IMAGES;

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

        ($type === 'blog') ? $pathImage = ConstantConfig::PATH_BLOG_IMAGES : $pathImage = ConstantConfig::PATH_PAGE_IMAGES;

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

        if ($this->model->deleteArticle($articleId)) {
            echo json_encode(['status' => "success", 'message' => 'The article has been successfully deleted.']);
        } else {
            echo json_encode(['status' => "danger", 'message' => 'An unexpected error occurred!']);
        }
    }

    public function ajaxDeleteSectionMethod(): void
    {
        $sectionId = (int) $this->requestData('id');

        if ($this->model->deleteSection($sectionId)) {
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
            $arrayResult = $imageUpload->createImages($fileTempName, $fileName, ConstantConfig::PATH_SECTION_IMAGES);

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
            [ConstantConfig::PATH_SECTION_IMAGES . 'photo/' . $file, ConstantConfig::PATH_SECTION_IMAGES . 'thumb/' . $file]
        );

        if ($deleteImages !== null) {
            echo json_encode(['status' => "danger", 'message' => $deleteImages]);
        } else {
            echo json_encode(['status' => "success", 'message' => "The image has been successfully deleted."]);
        }
    }
}
