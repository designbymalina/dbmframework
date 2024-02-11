<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;

class BlogModel
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getJoinArticlesLimit(int $limit): ?array
    {
        $query = "SELECT article.id AS aid, article.image_thumb, article.page_header, article.page_content, section.id AS sid, section.section_name, details.user_id AS uid, details.fullname"
            . " FROM dbm_article article"
            . " JOIN dbm_article_sections section ON section.id = article.section_id"
            . " JOIN dbm_user_details details ON details.user_id = article.user_id"
            . " ORDER BY article.created DESC LIMIT :limit";

        $this->database->queryExecute($query, [':limit' => $limit]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getJoinSectionArticles(int $id): ?array
    {
        $query = "SELECT article.id AS aid, article.image_thumb, article.page_header, article.page_content, section.id AS sid, section.section_name, details.user_id AS uid, details.fullname"
            . " FROM dbm_article article"
            . " JOIN dbm_article_sections section ON section.id = article.section_id"
            . " JOIN dbm_user_details details ON details.user_id = article.user_id"
            . " WHERE section.id = :id ORDER BY article.created DESC";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getJoinArticle(int $id): ?object
    {
        $query = "SELECT article.id AS aid, article.page_header, article.page_content, article.meta_title, article.meta_description, article.meta_keywords"
            . ", section.id AS sid, section.section_name, details.user_id AS uid, details.fullname"
            . " FROM dbm_article article"
            . " JOIN dbm_article_sections section ON section.id = article.section_id"
            . " JOIN dbm_user_details details ON details.user_id = article.user_id"
            . " WHERE article.id = :id LIMIT 1";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    public function getSection(int $id): ?array
    {
        $query = "SELECT * FROM dbm_article_sections WHERE id = :id LIMIT 1";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetch();
    }

    public function getSections(): ?array
    {
        $query = "SELECT * FROM dbm_article_sections ORDER BY id ASC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }
}
