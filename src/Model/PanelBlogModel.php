<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;

class PanelBlogModel
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getJoinArticlesFirst(): ?array
    {
        $query = "SELECT article.id AS aid, article.page_header, article.created, article.modified, section.section_name, user_details.fullname"
            . " FROM dbm_article article"
            . " INNER JOIN dbm_article_sections section ON section.id = article.section_id"
            . " INNER JOIN dbm_user_details user_details ON user_details.id = article.user_id"
            . " ORDER BY article.created DESC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getAllSections(): ?array
    {
        $query = "SELECT * FROM dbm_article_sections ORDER BY id DESC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getAllUsers(): ?array
    {
        $query = "SELECT user.id, user.login, details.fullname FROM dbm_user user"
            . " JOIN dbm_user_details details ON details.user_id = user.id"
            . " ORDER BY user.id DESC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getArticle(int $id): ?object
    {
        $query = "SELECT * FROM dbm_article WHERE id = :id";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    public function getLastId(): ?string
    {
        return $this->database->getLastInsertId();
    }

    public function insertArticle(array $data): bool
    {
        [$columns, $placeholders, $filteredData] = $this->database->buildInsertQuery($data);

        $query = "INSERT INTO dbm_article ($columns) VALUES ($placeholders)";

        return $this->database->queryExecute($query, $filteredData);
    }

    public function updateArticle($data): bool
    {
        [$setClause, $filteredData] = $this->database->buildUpdateQuery($data);

        $query = "UPDATE dbm_article SET $setClause WHERE id=:id";

        return $this->database->queryExecute($query, $filteredData);
    }

    public function deleteArticle(int $id): bool
    {
        $query = "DELETE FROM dbm_article WHERE id = :id";

        return $this->database->queryExecute($query, [':id' => $id]);
    }

    public function getSection(int $id): ?array
    {
        $query = "SELECT * FROM dbm_article_sections WHERE id = :id";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetch();
    }

    public function insertSection(array $data): bool
    {
        [$columns, $placeholders, $filteredData] = $this->database->buildInsertQuery($data);

        $query = "INSERT INTO dbm_article_sections ($columns) VALUES ($placeholders)";

        return $this->database->queryExecute($query, $filteredData);
    }

    public function updateSection($data): bool
    {
        [$setClause, $filteredData] = $this->database->buildUpdateQuery($data);

        $query = "UPDATE dbm_article_sections SET $setClause WHERE id=:id";

        return $this->database->queryExecute($query, $filteredData);
    }

    public function deleteSection(int $id): bool
    {
        $query = "DELETE FROM dbm_article_sections WHERE id = :id";

        return $this->database->queryExecute($query, [':id' => $id]);
    }
}
