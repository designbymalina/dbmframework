<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Interfaces\DatabaseInterface;

class UserModel
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function userAccount(int $id): ?object
    {
        $query = "SELECT details.*, user.id, user.email, user.avatar FROM dbm_user_details details"
            . " JOIN dbm_user user ON details.user_id = user.id"
            . " WHERE user.id = :id";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }
}
