<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;

class PanelModel
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getAllArticlesLimit(int $limit): ?array
    {
        $query = "SELECT page_header FROM dbm_article ORDER BY created DESC LIMIT :limit";

        $this->database->queryExecute($query, [':limit' => $limit]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchAllObject();
    }
}
