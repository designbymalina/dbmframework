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
use App\Form\PanelBlogForm;
use App\Model\PanelBlogModel;
use Dbm\Interfaces\DatabaseInterface;
use DateTime;

class PanelBlogService
{
    private $model;
    private $form;

    public function __construct(DatabaseInterface $database)
    {
        $this->model = new PanelBlogModel($database);
        $this->form = new PanelBlogForm();
    }

    public function prepareMeta(int $id): array
    {
        if (!empty($id) && $id !== 0) {
            return [
                'meta.title' => "Article editing - Dashboard DbM Framework",
            ];
        } else {
            return [
                'meta.title' => "Article create - Dashboard DbM Framework",
            ];
        }
    }

    public function preparePageData(int $id): array
    {
        if (!empty($id) && $id !== 0) {
            return [
                'header' => "Editing article",
                'action' => "editBlog",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'id' => $id,
            ];
        } else {
            return [
                'header' => "Create article",
                'action' => "createBlog",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'id' => $id,
                'accordion' => true,
            ];
        }
    }

    public function prepareFields(?object $dataArticle): ?object
    {
        if (!$dataArticle) {
            return null;
        }

        return (object) [
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

    public function getFormattedSections(): array
    {
        return array_reduce($this->model->getAllSections(), function ($result, $section) {
            $result[$section->id] = $section->section_name;
            return $result;
        }, []);
    }

    public function getFormattedUsers(): array
    {
        return array_reduce($this->model->getAllUsers(), function ($result, $user) {
            $result[$user->id] = $user->fullname !== null
                ? $user->fullname . ' (' . $user->login . ')'
                : $user->login;
            return $result;
        }, []);
    }

    public function prepareMetaArticle(): array
    {
        return [
            'meta.title' => "Article create - Dashboard DbM Framework",
        ];
    }

    public function preparePageArticle(): array
    {
        return [
            'header' => "Create article",
            'action' => "createBlog",
            'submit' => '<i class="fas fa-plus mr-2"></i>Create',
            'accordion' => true,
        ];
    }

    public function prepareFieldsArticle(array $formData): object
    {
        return (object) [
            'keywords' => $formData['keywords'],
            'description' => $formData['description'],
            'title' => $formData['title'],
            'header' => $formData['header'],
            'content' => $formData['content'],
            'sid' => $formData['section'],
            'uid' => $formData['user'],
            'image' => $formData['image'],
        ];
    }

    public function getArticleFormData(object $request): array
    {
        return [
            'id' => (int) $request->requestData('id') ?? null,
            'keywords' => $request->requestData('keywords'),
            'description' => $request->requestData('description'),
            'title' => $request->requestData('title'),
            'header' => $request->requestData('header'),
            'content' => $request->requestData('content'),
            'section' => $request->requestData('section'),
            'user' => $request->requestData('user'),
            'image' => $request->requestData('image') ?? null,
        ];
    }

    public function makeValidateArticleForm(array $formData): array
    {
        return $this->form->validateArticleForm(
            $formData['keywords'],
            $formData['description'],
            $formData['title'],
            $formData['header'],
            $formData['content'],
            $formData['section'],
            $formData['user']
        );
    }

    public function prepareInsertArticle(array $formData): array
    {
        return [
            'user_id' => (int) $formData['user'],
            'section_id' => (int) $formData['section'],
            'meta_title' => $formData['title'],
            'meta_description' => $formData['description'],
            'meta_keywords' => $formData['keywords'],
            'page_header' => $formData['header'],
            'page_content' => $formData['content'],
            'image_thumb' => !empty($formData['image']) ? $formData['image'] : null,
        ];
    }

    public function makeInsertArticle(array $sqlInsert): ?string
    {
        if ($this->model->insertArticle($sqlInsert)) {
            return $this->model->getLastId();
        }

        return null;
    }

    public function prepareUpdateArticle(array $formData)
    {
        $dateNow = (new DateTime())->format('Y-m-d H:i:s');

        return [
            'id' => $formData['id'],
            'user_id' => $formData['user'],
            'section_id' => $formData['section'],
            'meta_title' => $formData['title'],
            'meta_description' => $formData['description'],
            'meta_keywords' => $formData['keywords'],
            'page_header' => $formData['header'],
            'page_content' => $formData['content'],
            'image_thumb' => $formData['image'],
            'modified' => $dateNow,
        ];
    }

    public function prepareMetaSection(int $id): array
    {
        if (!empty($id) && $id !== 0) {
            return [
                'meta.title' => "Section editing - Dashboard DbM Framework",
            ];
        } else {
            return [
                'meta.title' => "Section create - Dashboard DbM Framework",
            ];
        }
    }

    public function preparePageSection(int $id): array
    {
        if (!empty($id) && $id !== 0) {
            return [
                'header' => "Editing section",
                'action' => "editSection",
                'submit' => '<i class="fa fa-edit mr-2"></i>Edit',
                'id' => $id,
            ];
        } else {
            return [
                'header' => "Create section",
                'action' => "createSection",
                'submit' => '<i class="fas fa-plus mr-2"></i>Create',
                'id' => $id,
            ];
        }
    }

    public function prepareFieldsSection(?array $dataSection): ?object
    {
        if (!$dataSection) {
            return null;
        }

        $keyMap = [
            'section_name' => 'name',
            'section_keywords' => 'keywords',
            'section_description' => 'description',
            'image_thumb' => 'image'
        ];

        foreach ($keyMap as $oldKey => $newKey) {
            if (isset($dataSection[$oldKey]) && !isset($dataSection[$newKey])) {
                $dataSection[$newKey] = $dataSection[$oldKey];
                unset($dataSection[$oldKey]);
            }
        }

        return (object) $dataSection;
    }

    public function getSectionFormData(object $request): array
    {
        $data = [
            'id' => (int) $request->requestData('id') ?? null,
            'name' => $request->requestData('name'),
            'keywords' => $request->requestData('keywords'),
            'description' => $request->requestData('description'),
            'image' => !empty($request->requestData('image')) ? $request->requestData('image') : null
        ];

        return $data;
    }

    public function createSection(array $formData): ?string
    {
        $image = !empty($formData['image']) ? $formData['image'] : null;

        $sqlInsert = [
            'section_name' => $formData['name'],
            'section_description' => $formData['description'],
            'section_keywords' => $formData['keywords'],
            'image_thumb' => $image,
        ];

        if ($this->model->insertSection($sqlInsert)) {
            return $this->model->getLastId();
        }

        return null;
    }

    public function updateSection(array $formData): bool
    {
        $image = !empty($formData['image']) ? $formData['image'] : null;

        $sqlUpdate = [
            'id' => $formData['id'],
            'section_name' => $formData['name'],
            'section_keywords' => $formData['keywords'],
            'section_description' => $formData['description'],
            'image_thumb' => $image,
        ];

        return $this->model->updateSection($sqlUpdate);
    }

    // TODO! Change PATH_BLOG_IMAGES to PATH_ARTICLE_IMAGES
    public function getTypeModule(string $type): string
    {
        switch ($type) {
            case 'article':
                $pathImage = ConstantConfig::PATH_BLOG_IMAGES;
                break;
            case 'section':
                $pathImage = ConstantConfig::PATH_SECTION_IMAGES;
                break;
            default:
                $pathImage = ConstantConfig::PATH_PAGE_IMAGES;
        }

        return $pathImage;
    }
}
