<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\FuncEntity;

/**
 * Description of ClubStatsList
 *
 * @author TOM
 */
class ClubStatsList extends FuncEntity {

    private
            $competition,
            $clubStatsList = array();

    public function setCompetition($competitionId) {
        $this->competition = new Competitions($this->database);
        $this->competition->setId($competitionId);
    }

    public function getClubStatsList() {
        return $this->clubStatsList;
    }

    protected function checkSetters() {
        if (is_null($this->matchTypeId) || is_null($this->competition->getId()) || is_null($this->seasonYear)) {
            throw new \Exception("Chyba nastavení třídy " . get_class() . " - nebyly nastaveny všechny parametry");
        }
    }

    private function setClubStat($clubStatData) {
        foreach ($clubStatData as $clubStat) {
            $newClubStats = new ClubStats($this->database);
            $newClubStats->setClubStats($clubStat);
            $this->clubStatsList[] = $newClubStats;
        }
    }

    public function calcClubStatsList() {
        try {
            //$this->checkSetters();
            $values = $this->readClubStats();
            $this->setClubStat($values);
        } catch (Exception $e) {
            return $e;
        }
    }
    
    public function calcClubStatsListMini() {
        try {
            $values = $this->readClubStatsMini();
            $this->setClubStat($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    private function readClubStats() {
        return $this->database->query('SELECT * FROM tymy_stat_vse_new(?,?,?,?)', !is_null($this->matchTypeId) ? (int) $this->matchTypeId : 0, !is_null($this->competition) ? (int) $this->competition->getId() : 0, !is_null($this->seasonYear) ? (int) $this->seasonYear : 0, 0 /*id_klub*/)->fetchAll();
    }
    
    private function readClubStatsMini() {
        return $this->database->query('SELECT * FROM tymy_stat_vse_new(?,?,?,?)', 0,0,(int) $this->seasonYear,0)->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
