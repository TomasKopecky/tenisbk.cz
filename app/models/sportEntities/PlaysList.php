<?php

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Plays;

/**
 * Description of PlayStats
 *
 * @author TOM
 */
class PlaysList extends Plays {

    private $playsList = array();

    private function setPlaysList($playsData) {
        foreach ($playsData as $play) {
            $newPlay = new Plays($this->database);
            $newPlay->setPlay($play);
            $this->playsList[] = $newPlay;
        }
    }

    public function calcPlaysList() {
        try {
            $values = $this->readPlaysList();
            $this->setPlaysList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getPlaysList() {
        return $this->playsList;
    }

    private function readPlaysList() {
        return $this->database->query('SELECT * FROM hrac ORDER BY jmeno')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

