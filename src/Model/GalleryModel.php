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

    /* TEMPORARY - TO REMOVE! */
    public function getGalleryPhotos(): ?array
    {
        $query = "SELECT * FROM dbm_gallery WHERE status=true ORDER BY id DESC"; // LIMIT 12

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getGalleryLoadData($limit, $start): ?array
    {
        $query = "SELECT * FROM dbm_gallery WHERE status=true ORDER BY id DESC LIMIT $start, $limit";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function countPhotos(): ?array
    {
        $query = "SELECT COUNT(*) AS all_photos FROM dbm_gallery";

        $this->database->queryExecute($query); // Zmien na querySQL()

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetch();
    }
}
