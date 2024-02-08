<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\MatchesTable;

/**
 * Třída MatchesTableList
 *
 * @author Tomáš Kopecký
 */
class MatchesTableList extends MatchesTable {

    private $matchesTableList = array();

    private function setMatchesTableList($matchesData) {
        foreach ($matchesData as $match) {
            $newMatch = new MatchesTable($this->database);
            $newMatch->setMatchesTable($match);
            $this->matchesTableList[] = $newMatch;
        }
    }

    private function readMatchesTableList() {
        return $this->database->query('SELECT * FROM tymy_zapasy(?,?,?,?,?,?,?,?)', 
                $this->getSeasonYear() ? (int) $this->getSeasonYear() : 0, 
                !is_null($this->getRound()) ? (int) $this->getRound()->getNumber() : 0,
                !is_null($this->getCompetition()) ? (int) $this->getCompetition()->getId() : 0,
                0, !is_null($this->getPlay()) ? (int) $this->getPlay()->getId() : 0,
                0, !is_null($this->getPlayer()) ? (int) $this->getPlayer()->getId() : 0,
                $this->getMatchTypeId() ? (int) $this->getMatchTypeId() : 0)->fetchAll();
    }

    public function calcMatchesTableList() {
        try {
            $this->setMatchesTableList($this->readMatchesTableList());
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function getMatchesTableList() {
        return $this->matchesTableList;
    }

    public function getNewInstance() {
        return new MatchesTableList($this->database);
    }

}
