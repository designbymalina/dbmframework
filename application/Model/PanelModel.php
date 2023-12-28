<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

namespace App\Model;

use Dbm\Classes\DatabaseClass;

class PanelModel extends DatabaseClass
{
    public function getAllArticlesLimit(int $limit): array
    {
        $query = "SELECT page_header FROM dbm_article ORDER BY created DESC LIMIT :limit";

        $this->queryExecute($query, [':limit' => $limit]);

        if ($this->rowCount() > 0) {
            return $this->fetchAllObject();
        }

        return [];
    }

    public function getJoinArticlesFirst(): array
    {
        $query = "SELECT article.id AS aid, article.page_header, article.created, article.modified, section.section_name, user_details.fullname"
            . " FROM dbm_article article"
            . " INNER JOIN dbm_article_sections section ON section.id = article.section_id"
            . " INNER JOIN dbm_user_details user_details ON user_details.id = article.user_id"
            . " ORDER BY article.created DESC";

        $this->queryExecute($query);

        if ($this->rowCount() > 0) {
            return $this->fetchAllObject();
        }

        return [];
    }

    public function getAllSections(): array
    {
        $query = "SELECT * FROM dbm_article_sections ORDER BY id DESC";

        $this->queryExecute($query);

        if ($this->rowCount() > 0) {
            return $this->fetchAllObject();
        }

        return [];
    }

    public function getAllUsers(): array
    {
        $query = "SELECT user.id, user.login, details.fullname FROM dbm_user user"
            . " JOIN dbm_user_details details ON details.user_id = user.id"
            . " ORDER BY user.id DESC";

        $this->queryExecute($query);

        if ($this->rowCount() > 0) {
            return $this->fetchAllObject();
        }

        return [];
    }

    public function getArticle(int $id): object
    {
        $query = "SELECT * FROM dbm_article WHERE id = :id";

        $this->queryExecute($query, [':id' => $id]);

        if ($this->rowCount() > 0) {
            return $this->fetchObject();
        }

        return (object) [];
    }

    public function getLastId(): int
    {
        return $this->lastInsertId();
    }

    public function insertArticle(array $data): bool
    {
        $query = "INSERT INTO dbm_article (user_id, section_id, meta_title, meta_description, meta_keywords, page_header, page_content, image_thumb)"
            ." VALUES (:uid, :sid, :title, :description, :keywords, :header, :content, :thumb)";

        return $this->queryExecute($query, $data);
    }

    public function updateArticle($data): bool
    {
        $query = "UPDATE dbm_article"
            . " SET user_id=:uid, section_id=:sid, meta_title=:title, meta_description=:description, meta_keywords=:keywords"
            . ", page_header=:header, page_content=:content, image_thumb=:thumb, modified=:date"
            . " WHERE id = :id";

        return $this->queryExecute($query, $data);
    }

    public function deleteArticle(int $id): bool
    {
        $query = "DELETE FROM dbm_article WHERE id = :id";

        return $this->queryExecute($query, [':id' => $id]);
    }

    public function getSection(int $id): object
    {
        $query = "SELECT * FROM dbm_article_sections WHERE id = :id";

        $this->queryExecute($query, [':id' => $id]);

        if ($this->rowCount() > 0) {
            return $this->fetchObject();
        }

        return (object) [];
    }

    public function updateSection($data): bool
    {
        $query = "UPDATE dbm_article_sections"
            . " SET section_name = :name, section_description = :description, section_keywords = :keywords, image_thumb = :thumb"
            . " WHERE id = :id";

        return $this->queryExecute($query, $data);
    }

    public function insertSection(array $data): bool
    {
        $query = "INSERT INTO dbm_article_sections (section_name, section_description, section_keywords, image_thumb)"
            . " VALUES (:name, :description, :keywords, :thumb)";
            
        return $this->queryExecute($query, $data);
    }

    public function deleteSection(int $id): bool
    {
        $query = "DELETE FROM dbm_article_sections WHERE id = :id";

        return $this->queryExecute($query, [':id' => $id]);
    }

    public function arraySections(): array
    {
        $result = [];
        $sections = $this->getAllSections();

        foreach ($sections as $value) {
            $result[$value->id] = $value->section_name;
        }

        return $result;
    }

    public function arrayUsers(): array
    {
        $result = [];
        $users = $this->getAllUsers();

        foreach ($users as $value) {
            if ($value->fullname !== null) {
                $result[$value->id] = $value->fullname . ' (' . $value->login .')';
            } else {
                $result[$value->id] = $value->login;
            }
        }

        return $result;
    }

    public function validateFormBlog(string $keywords, string $description, string $title, string $header, string $content, string $section, ?string $user): array
    {
        $data = [];

        if (empty($keywords)) {
            $data['errorKeywords'] = "The keywords field is required!";
        }

        if (empty($description)) {
            $data['errorDescription'] = "The description field is required!";
        }

        if (empty($title)) {
            $data['errorTitle'] = "The title field is required!";
        } elseif ((mb_strlen($title) < 3) || (mb_strlen($title) > 65)) {
            $data['errorTitle'] = "The header must contain from 3 to 65 characters!";
        }

        if (empty($header)) {
            $data['errorHeader'] = "The header field is required!";
        } elseif ((mb_strlen($header) < 10) || (mb_strlen($header) > 120)) {
            $data['errorHeader'] = "The header must contain from 10 to 120 characters!";
        }

        if (empty($content)) {
            $data['errorContent'] = "The content field is required!";
        } elseif (mb_strlen($content) < 1000) {
            $data['errorContent'] = "The content must contain minimum 1000 characters!";
        }

        if (empty($section)) {
            $data['errorSection'] = "The section field is required!";
        }

        if (empty($user)) {
            $data['errorUser'] = "The user field is required!";
        }

        return $data;
    }

    public function validateFormBlogSection(string $name, string $description, string $keywords): array
    {
        $data = [];

        if (empty($name)) {
            $data['errorName'] = "The name field is required!";
        } elseif ((mb_strlen($name) < 3) || (mb_strlen($name) > 100)) {
            $data['errorName'] = "The header must contain from 3 to 100 characters!";
        }

        if (empty($keywords)) {
            $data['errorKeywords'] = "The keywords field is required!";
        }

        if (empty($description)) {
            $data['errorDescription'] = "The description field is required!";
        }

        return $data;
    }
}
