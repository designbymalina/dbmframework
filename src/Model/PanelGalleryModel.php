<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;

class PanelGalleryModel
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getGalleryPhotos(): ?array
    {
        $query = "SELECT * FROM dbm_gallery ORDER BY id DESC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getPhoto(int $id): ?object
    {
        $query = "SELECT * FROM dbm_gallery WHERE id = :id";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    public function insertPhoto(array $data): bool
    {
        $query = "INSERT INTO dbm_gallery (user_id, filename, title, description)"
            . " VALUES (:uid, :filename, :title, :description)";

        return $this->database->queryExecute($query, $data);
    }

    public function updatePhoto($data): bool
    {
        $query = "UPDATE dbm_gallery"
            . " SET title=:title, description=:description, status=:status, modified=:date"
            . " WHERE id = :id";

        return $this->database->queryExecute($query, $data);
    }

    public function deletePhoto(int $id): bool
    {
        $query = "DELETE FROM dbm_gallery WHERE id = :id";

        return $this->database->queryExecute($query, [':id' => $id]);
    }

    public function validateFormGallery(string $title, string $photoStatus, string $photoMessage): array
    {
        $data = [];

        if (empty($title)) {
            $data['errorTitle'] = "The title field is required!";
        } elseif ((mb_strlen($title) < 3) || (mb_strlen($title) > 65)) {
            $data['errorTitle'] = "The header must contain from 3 to 65 characters!";
        }

        if ($photoStatus == 'Warning') {
            $data['errorPhoto'] = "The photo field is required!";
        } elseif ($photoStatus == 'danger') {
            $data['errorPhoto'] = $photoMessage;
        }

        return $data;
    }
}
