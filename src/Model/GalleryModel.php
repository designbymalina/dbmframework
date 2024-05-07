<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;

class GalleryModel
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getGalleryPhotos(): ?array
    {
        $query = "SELECT * FROM dbm_gallery WHERE status=true ORDER BY id DESC"; // LIMIT 12

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function countGalleryPhotos(int $lastID): ?array
    {
        $query = "SELECT COUNT(*) AS num_rows FROM dbm_gallery WHERE id < $lastID ORDER BY id DESC";

        $this->database->queryExecute($query); // Zmien na querySQL()

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetch();
    }

    public function getCountGalleryPhotos(int $lastID): ?array
    {
        $query = "SELECT * FROM dbm_gallery WHERE id < $lastID ORDER BY id DESC LIMIT 3";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }
}
