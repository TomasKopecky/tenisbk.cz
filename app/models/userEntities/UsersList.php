<?php

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\UserEntity\Users;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class UsersList extends Users {

    private $usersList = array();

    private function setUsersList($usersData) {
        foreach ($usersData as $users) {
            $newUser = new Users($this->database);
            $newUser->setUser($users);
            $this->usersList[] = $newUser;
        }
    }

    public function calcUsersList() {
        try {
            $values = $this->readUsersList();
            $this->setUsersList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getUsersList() {
        return $this->usersList;
    }

    private function readUsersList() {
        return $this->database->query('SELECT * FROM uzivatele.uzivatele ORDER BY id_uzivatel')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

