<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class PlayerStats extends BasicEntity {

    private
            $matches,
            $wins,
            $defeats,
            $wonSets,
            $lostSets,
            $wonGames,
            $lostGames,
            $successRate,
            $truePoints,
            $player,
            $rankingPosition;

    private function checkSetters() {
        if (is_null($this->matchTypeId) || is_null($this->getPlayer()) || is_null($this->seasonYear)) {
            throw new \Exception("Chyba nastavení třídy " . get_class() . " - nebyly nastaveny všechny parametry");
        }
    }

    public function setMatches($matches) {
        $this->matches = $matches;
    }

    public function setWins($wins) {
        $this->wins = $wins;
    }

    public function setDefeats($defeats) {
        $this->defeats = $defeats;
    }

    public function setWonSets($WonSets) {
        $this->wonSets = $WonSets;
    }

    public function setLostSets($lostSets) {
        $this->lostSets = $lostSets;
    }

    public function setWonGames($WonGames) {
        $this->wonGames = $WonGames;
    }

    public function setLostGames($lostGames) {
        $this->lostGames = $lostGames;
    }

    public function setSuccessRate($successRate) {
        $this->successRate = $successRate;
    }
    
    public function setTruePoints($truePoints) {
        $this->truePoints = $truePoints;
    }

    public function setPlayer(Players $player) {
        $this->player = $player;
    }

    public function setRankingPosition($rankingPosition) {
        $this->rankingPosition = $rankingPosition;
    }

    protected function setPlayerStats($playerStatData) {
        if ($playerStatData) {
            $player = new Players($this->database);
            isset($playerStatData->id) ? $player->setId($playerStatData->id) : NULL;
            isset($playerStatData->slug) ? $player->setSlug($playerStatData->slug) : NULL;
            isset($playerStatData->jme) ? $player->setName($playerStatData->jme) : NULL;
            $this->player = $player;
            isset($playerStatData->roc) ? $this->setSeasonYear($playerStatData->roc) : NULL;
            isset($playerStatData->typ_zap) ? $this->setMatchTypeId($playerStatData->typ_zap) : NULL;
            isset($playerStatData->zap) ? $this->matches = $playerStatData->zap : NULL;
            isset($playerStatData->vyh) ? $this->wins = $playerStatData->vyh : NULL;
            isset($playerStatData->pro) ? $this->defeats = $playerStatData->pro : NULL;
            isset($playerStatData->set_vyh) ? $this->wonSets = $playerStatData->set_vyh : NULL;
            isset($playerStatData->set_pro) ? $this->lostSets = $playerStatData->set_pro : NULL;
            isset($playerStatData->gam_vyh) ? $this->wonGames = $playerStatData->gam_vyh : NULL;
            isset($playerStatData->gam_pro) ? $this->lostGames = $playerStatData->gam_pro : NULL;
            isset($playerStatData->usp) ? $this->successRate = $playerStatData->usp : NULL;
            isset($playerStatData->bod) ? $this->truePoints = $playerStatData->bod: NULL;
        }
    }

    public function getMatches() {
        return $this->matches;
    }

    public function getWins() {
        return $this->wins;
    }

    public function getDefeats() {
        return $this->defeats;
    }

    public function getWonSets() {
        return $this->wonSets;
    }

    public function getLostSets() {
        return $this->lostSets;
    }

    public function getWonGames() {
        return $this->wonGames;
    }

    public function getLostGames() {
        return $this->lostGames;
    }

    public function getPlayer() {
        return $this->player;
    }

    public function getRankingPosition() {
        return $this->rankingPosition;
    }

    public function getSuccessRate() {
        return $this->successRate;
    }
    
    public function getTruePoints() {
        return $this->truePoints;
    }

    public function calcPlayersStats() {
        try {
            $this->checkSetters();
            $values = $this->readAllStats();
            $this->setPlayerStats($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    protected function readRankingPosition() {
        return $this->database->query('WITH ranking AS (SELECT id, ROW_NUMBER() OVER () FROM hraci_stat_zebricek(?,?,?)) SELECT row_number FROM ranking WHERE id = ?', $this->getMatchTypeId(), $this->player->getSex(), $this->getSeasonYear(), $this->player->getId())->fetchField();
    }

    protected function readAllStats() {
        return $this->database->query('SELECT * from hraci_stat_osobni(?,?,?)', (int)$this->getMatchTypeId(), (int)$this->getSeasonYear(), (int)$this->getPlayer()->getId())->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
