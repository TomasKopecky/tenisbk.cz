<?php

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\UserEntity\RoleLogs;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class RoleLogsList extends RoleLogs {

    private $roleLogsList = array();

    private function setRoleLogsList($roleLogsData) {
        foreach ($roleLogsData as $roleLog) {
            $newRoleLog = new RoleLogs($this->database);
            $newRoleLog->setRoleLog($roleLog);
            $this->roleLogsList[] = $newRoleLog;
        }
    }

    public function calcRoleLogsList() {
        try {
            $values = $this->readRoleLogsList();
            $this->setRoleLogsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getRoleLogsList() {
        return $this->roleLogsList;
    }
    private function readRoleLogsList() {
        return $this->database->query('SELECT O.id_opravneni, U.jmeno, U.uzivatelske_jmeno, U.aktivni, R.nazev FROM uzivatele.uzivatele U NATURAL JOIN uzivatele.opravneni O NATURAL JOIN uzivatele.role R ORDER BY id_opravneni')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

