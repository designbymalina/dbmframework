<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Classes\DatabaseClass;

class UserModel extends DatabaseClass
{
    public function userAccount(int $id): ?object
    {
        $query = "SELECT details.*, user.id, user.email, user.avatar FROM dbm_user_details details"
            . " JOIN dbm_user user ON details.user_id = user.id"
            . " WHERE user.id = :id";

        $this->queryExecute($query, [':id' => $id]);

        if ($this->rowCount() > 0) {
            return $this->fetchObject();
        }
        
        return null;
    }
}
