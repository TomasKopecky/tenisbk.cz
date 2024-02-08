<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\UserEntity\Users;
use App\Model\Entity\UserEntity\Roles;
use Nette\Utils\DateTime;

/**
 * Description of RoleLogs
 *
 * @author inComputer
 */
class RoleLogs extends BasicEntity {

    private $id,
            $user,
            $role,
            $dateSince,
            $dateUntil,
            $descriptions;

    public function setId($id) {
        $this->id = $id;
    }

    public function setUser(Users $user) {
        $this->user = $user;
    }

    public function setRole(Roles $role) {
        $this->role = $role;
    }

    public function setDateSince($dateSince) {
        $this->dateSince = $dateSince;
    }

    public function setDateUntil($dateUntil) {
        $this->dateUntil = $dateUntil;
    }

    public function setDescritpions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function getId() {
        return $this->id;
    }

    public function getUser() {
        return $this->user;
    }

    public function getRole() {
        return $this->role;
    }

    public function getDateSince() {
        return $this->dateSince;
    }

    public function getDateUntil() {
        return $this->dateUntil;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    protected function setRoleLog($roleLogData) {
        if ($roleLogData) {
            $this->id = $roleLogData->id_opravneni ?? NULL;
            $user = new Users($this->database);
            $user->setId(isset($roleLogData->id_uzivatel) ? $roleLogData->id_uzivatel : NULL);
            $user->setName(isset($roleLogData->jmeno) ? $roleLogData->jmeno : NULL);
            $user->setUsername(isset($roleLogData->uzivatelske_jmeno) ? $roleLogData->uzivatelske_jmeno : NULL);
            $user->setActiveStatus(isset($roleLogData->aktivni) ? $roleLogData->aktivni : NULL);
            $this->user = $user;
            $role = new Roles($this->database);
            $role->setId(isset($roleLogData->id_role) ? $roleLogData->id_role : NULL);
            $role->setTitle(isset($roleLogData->nazev) ? $roleLogData->nazev : NULL);
            $this->role = $role;
            $this->dateSince = isset($roleLogData->datum_od) ?? $roleLogData->datum_od != '' ?? NULL;
            $this->dateUntil = isset($roleLogData->datum_do) ?? $roleLogData->datum_do != '' ?? NULL;
            $this->descriptions = isset($roleLogData->opravneni_info) && $roleLogData->opravneni_info != '' ? $roleLogData->opravneni_info : NULL;
        }
    }

    public function insertRoleLog($roleLogData) {
        try {
            $this->setRoleLog($roleLogData);
            $this->createRoleLog();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateRoleLog($id, $roleLogData) {
        try {
            $this->setRoleLog($roleLogData);
            $this->setId($id);
            $this->editRoleLog();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteRoleLog() {
        try {
            $this->eraseRoleLog();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcRoleLog() {
        try {
            $values = $this->readRoleLog();
            $this->setRoleLog($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_opravneni VALUES (?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->user->getId(), $this->role->getId(), DateTime::from('now'), NULL, $this->descriptions, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_opravneni VALUES (?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->user->getId(), $this->role->getId(), DateTime::from('now'), NULL, $this->descriptions, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_opravneni VALUES (?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->user->getId(), $this->role->getId(), DateTime::from('now'), NULL, $this->descriptions, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createRoleLog() {
        return $this->database->query('SELECT * FROM uzivatele.opravneni_vkladani(?,?,?,?)', (int) $this->user->getId(), (int) $this->role->getId(), date('Y-m-d h:i:s'), $this->descriptions)->fetch();
    }

    private function editRoleLog() {
        return $this->database->query('SELECT * FROM uzivatele.opravneni_uprava(?,?,?,?,?)', (int) $this->id, (int) $this->user->getId(), (int) $this->role->getId(), date('Y-m-d h:i:s'), $this->descriptions)->fetch();
    }

    private function readRoleLog() {
        return $this->database->query('SELECT * FROM uzivatele.opravneni NATURAL JOIN uzivatele.uzivatele NATURAL JOIN uzivatele.role WHERE id_opravneni = ?', (int) $this->id)->fetch();
    }

    private function eraseRoleLog() {
        return $this->database->query('DELETE FROM uzivatele.opravneni WHERE id_opravneni = ?', (int) $this->id)->fetch();
    }

    /*
      public function setRoleLog($userId, $role, $date, $descriptions) {
      $this->userId = $userId;
      $this->role = $role;
      $this->date = $date;
      $this->descriptions = $descriptions;
      }

      public function InsertNewRoleLog() {
      return $this->WriteRoleLogToDatabase();
      }

      public function getRoleLogById($id) {
      $this->id = $id;
      $this->readRoleLogFromDatabase();
      }

      public function getAllRoleLogs() {
      return $this->readAllRoleLogsFromDatabase();
      }

      private function WriteRoleLogToDatabase() {
      return $this->database->query('INSERT INTO uzivatele.role_log (user_id, role_id, role_start, role_log_descriptions) VALUES (?,?,?,?)', $this->userId, $this->role, $this->date, $this->descriptions)->fetch();
      }

      private function readRoleLogFromDatabase() {
      return $this->database->query('SELECT * FROM uzivatele.role_log WHERE role_log_id = ?', $this->id)->fetch();
      }

      private function readAllRoleLogsFromDatabase() {
      return $this->database->query('SELECT U.id_uzivatel, jmeno, uzivatelske_jmeno, aktivni, nazev FROM uzivatele.uzivatele U LEFT JOIN uzivatele.opravneni O ON (U.id_uzivatel = O.id_uzivatel) LEFT JOIN uzivatele.role R ON (O.id_role = R.id_role)')->fetchAll();
      }

      private function readRoleLogDuplicity() {
      throw new \Nette\NotImplementedException;
      //return $this->database->query('SELECT * FROM uzivatele.users WHERE username = ?', $this->userName)->fetch();
      }

     */

    public function getNewInstance() {
        return new self($this->database);
    }

}
