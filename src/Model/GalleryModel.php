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

    public function getGalleryPhotos(int $limit): ?array
    {
        $query = "SELECT * FROM dbm_gallery WHERE status='Active' ORDER BY id DESC LIMIT $limit";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }

    public function getGalleryLoadData(int $start, int $limit): ?array
    {
        $query = "SELECT * FROM dbm_gallery WHERE status='Active' ORDER BY id DESC LIMIT $start, $limit";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }
}
