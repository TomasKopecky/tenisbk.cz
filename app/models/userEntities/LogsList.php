<?php

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\UserEntity\Logs;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class LogsList extends Logs {

    private $logsList = array();

    private function setLogsList($logsData) {
        foreach ($logsData as $logs) {
            $newLog = new Logs($this->database);
            $newLog->setLog($logs);
            $this->logsList[] = $newLog;
        }
    }

    public function calcLogsList() {
        try {
            $values = $this->readLogsList();
            $this->setLogsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getLogsList() {
        return $this->logsList;
    }

    private function readLogsList() {
        return $this->database->query('SELECT UD.id_udalost, UD.nazev, L.datum, UZ.id_uzivatel, UZ.jmeno, L.uzivatelske_jmeno, L.heslo FROM uzivatele.log_udalosti L LEFT JOIN uzivatele.uzivatele UZ ON (L.id_uzivatel = UZ.id_uzivatel) NATURAL JOIN uzivatele.udalosti UD ORDER BY datum DESC LIMIT 500')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

