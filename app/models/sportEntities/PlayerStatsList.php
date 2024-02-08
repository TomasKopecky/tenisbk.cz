<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\PlayerStats;
use App\Model\Entity\SportEntity\Players;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class PlayerStatsList extends PlayerStats {

    private $playerStatsList = array();

    private function checkSetters() {
        if (is_null($this->matchTypeId) || is_null($this->playerSexValue) || is_null($this->seasonYear)) {
            throw new \Exception("Chyba nastavení třídy " . get_class() . " - nebyly nastaveny všechny parametry");
        }
    }

    private function setPlayerStatsList($playerStatData) {
        foreach ($playerStatData as $playerStat) {
            $newPlayerStats = new PlayerStats($this->database);
            $newPlayerStats->setPlayerStats($playerStat);
            $this->playerStatsList[] = $newPlayerStats;
        }
    }

    public function calcPlayersStatsList() {
        try {
            $this->checkSetters();
            $values = $this->readAllStatsList();
            $this->setPlayerStatsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function calcPlayerStatsParticular() {
        try {
            $this->checkSetters();
            $values = $this->readAllStats();
            $this->setPlayerStatsList($values);
            $this->setRankingPosition($this->readRankingPosition());
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getPlayerStatsList() {
        return $this->playerStatsList;
    }

    private function readAllStatsList() {
        return $this->database->query('SELECT * FROM hraci_stat_zebricek(?,?,?,?)', (int) $this->getMatchTypeId(), $this->getPlayerSexValue(), (int) $this->getSeasonYear(), !is_null($this->getPlayer()) ? (int) $this->getPlayer()->getId() : NULL)->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
