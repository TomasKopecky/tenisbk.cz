<?php

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\UserEntity\Roles;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class RolesList extends Roles {

    private $rolesList = array();

    private function setRolesList($rolesData) {
        foreach ($rolesData as $roles) {
            $newRole = new Roles($this->database);
            $newRole->setRole($roles);
            $this->rolesList[] = $newRole;
        }
    }

    public function calcRolesList() {
        try {
            $values = $this->readRolesList();
            $this->setRolesList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getRolesList() {
        return $this->rolesList;
    }

    private function readRolesList() {
        return $this->database->query('SELECT * FROM uzivatele.role ORDER BY id_role')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

