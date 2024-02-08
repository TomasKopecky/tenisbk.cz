<?php

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\CompSystems;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class CompSystemsList extends CompSystems {

    private $compsystemsList = array();

    private function setCompSystemsList($compsystemsData) {
        foreach ($compsystemsData as $compsystem) {
            $newCompSystem = new CompSystems($this->database);
            $newCompSystem->setCompSystem($compsystem);
            $this->compsystemsList[] = $newCompSystem;
        }
    }

    public function calcCompSystemsList() {
        try {
            $values = $this->readCompSystemsList();
            $this->setCompSystemsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getCompSystemsList() {
        return $this->compsystemsList;
    }

    private function readCompSystemsList() {
        return $this->database->query('SELECT * FROM soutez_system NATURAL JOIN soutez ORDER BY jmeno')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

