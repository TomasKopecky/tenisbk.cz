<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\PlaysTable;

/**
 * Description of MatchList
 *
 * @author inComputer
 */
class PlaysTableList extends PlaysTable {

    private $playsTableList = array();

    private function setPlaysTableList($playsData) {
        foreach ($playsData as $play) {
            $newPlay = new PlaysTable($this->database);
            $newPlay->setPlaysTable($play);
            $this->playsTableList[] = $newPlay;
        }
    }

    public function calcPlaysTableList() {
        try {
            $values = $this->readPlaysTableList();
            $this->setPlaysTableList($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function getPlaysTableList() {
        return $this->playsTableList;
    }

    private function readPlaysTableList() {
        return $this->database->query('SELECT * FROM tymy_utkani(?,?,?,?)',
                !is_null($this->getSeasonYear()) ? (int) $this->getSeasonYear() : 0, 
                !is_null($this->getRound()) ? (int) $this->getRound()->getNumber() : 0, 
                !is_null($this->getCompetition()) ? (int) $this->competition->getId() : 0, 
                !is_null($this->club) ? (int) $this->club->getId() : 0)
                ->fetchAll();
    }

    public function getNewInstance() {
        return new PlaysTableList($this->database);
    }

}
