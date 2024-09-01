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
        $query = "SELECT details.*, user.id, user.login, user.email, user.created"
            . " FROM dbm_user_details details"
            . " JOIN dbm_user user ON details.user_id = user.id"
            . " WHERE user.id = :id";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    public function getUser(int $id): ?object
    {
        $query = "SELECT * FROM dbm_user WHERE id = :id";

        $this->database->queryExecute($query, [':id' => $id]);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $this->database->fetchObject();
    }

    public function updatePassword(array $data): bool
    {
        $query = "UPDATE dbm_user SET password = :password WHERE id = :id";

        return $this->database->queryExecute($query, $data);
    }

    public function updateUserDetails(array $data): bool
    {
        $query = "UPDATE dbm_user_details SET fullname = :fullname, phone = :phone, website = :website";
        $query .= ", profession = :profession, business = :business, address = :address, biography = :biography";

        if (!empty($data['avatar'])) {
            $query .= ", avatar = :avatar";
        }

        $query .= " WHERE id = :id";

        return $this->database->queryExecute($query, $data);
    }
}
