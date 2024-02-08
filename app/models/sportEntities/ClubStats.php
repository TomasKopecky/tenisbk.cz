<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of ClubStats
 *
 * @author TOM
 */
class ClubStats extends BasicEntity {

    private
            $plays,
            $wins,
            $defeats,
            $matches,
            $matchesWon,
            $matchesLost,
            $sets,
            $setsWon,
            $setsLost,
            $games,
            $gamesWon,
            $gamesLost,
            $points,
            $club,
            $competition,
            $rankingPosition;

    public function setPlays($plays) {
        $this->plays = $plays;
    }

    public function setWins($wins) {
        $this->wins = $wins;
    }

    public function setDefeats($defeats) {
        $this->defeats = $defeats;
    }

    public function setMatches() {
        $this->matches = $this->matchesWon.':'.$this->matchesLost;
    }
    
    public function setMatchesWon($matchesWon) {
        $this->matchesWon = $matchesWon;
    }
    
    public function setMatchesLost($matchesLost) {
        $this->matchesLost = $matchesLost;
    }

    public function setSets() {
        $this->sets = $this->setsWon.':'.$this->setsLost;
    }

    public function setSetsWon($setsWon) {
        $this->setsWon = $setsWon;
    }
    
    public function setSetsLost($setsLost) {
        $this->setsLost = $setsLost;
    }
    
    public function setGames() {
        $this->games = $this->gamesWon.':'.$this->gamesLost;
    }
    
    public function setGamesWon($gamesWon) {
        $this->gamesWon = $gamesWon;
    }
    
    public function setGamesLost($gamesLost) {
        $this->gamesLost = $gamesLost;
    }

    public function setPoints($points) {
        $this->points = $points;
    }

    public function setClubs(Clubs $club) {
        $this->club = $club;
    }

    public function setCompetition(Competitions $competition) {
        $this->competition = $competition;
    }
    
    public function setRankingPosition($rankingPosition) {
        $this->rankingPosition = $rankingPosition;
    }

    public function getPlays() {
        return $this->plays;
    }

    public function getWins() {
        return $this->wins;
    }

    public function getDefeats() {
        return $this->defeats;
    }

    public function getMatches() {
        return $this->matches;
    }
    
    public function getMatchesWon() {
        return $this->matchesWon;
    }
    
    public function getMatchesLost() {
        return $this->matchesLost;
    }

    public function getSets() {
        return $this->sets;
    }
    
    public function getSetsWon() {
        return $this->setsWon;
    }
    
    public function getSetsLost() {
        return $this->setsLost;
    }

    public function getGames() {
        return $this->games;
    }
    
    public function getGamesWon() {
        return $this->gamesWon;
    }
    
    public function getGamesLost() {
        return $this->gamesLost;
    }

    public function getPoints() {
        return $this->points;
    }

    public function getClub() {
        return $this->club;
    }

    public function getCompetition() {
        return $this->competition;
    }
    
    public function getRankingPosition() {
        return $this->rankingPosition;
    }

    public function getClubStats() {
        try {
            $values = $this->readClubStats();
            $this->setClubStats($values);
            $this->setRankingPosition($this->readRankingPosition());
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function setClubStats($clubStatData) {
        if ($clubStatData) {
            $club = new Clubs($this->database);
            $club->setClub($clubStatData);
            $this->setClubs($club);
            $competition = new Competitions($this->database);
            isset($clubStatData->id_sou) ? $competition->setId($clubStatData->id_sou) : NULL;
            isset($clubStatData->sou_naz) ? $competition->setName($clubStatData->sou_naz) : NULL;
            $this->setCompetition($competition);
            $this->plays = $clubStatData->utk;
            $this->wins = $clubStatData->vyhry;
            $this->defeats = $clubStatData->pro;
            $this->matchesWon = isset($clubStatData->zap_vyh) ? $clubStatData->zap_vyh : NULL;
            $this->matchesLost = isset($clubStatData->zap_proh) ? $clubStatData->zap_proh : NULL;
            $this->setMatches();
            $this->setsWon = isset($clubStatData->set_vyh) ? $clubStatData->set_vyh : NULL;
            $this->setsLost = isset($clubStatData->set_proh) ? $clubStatData->set_proh : NULL;
            $this->setSets();
            $this->gamesWon = isset($clubStatData->gam_vyh) ? $clubStatData->gam_vyh : NULL;
            $this->gamesLost = isset($clubStatData->gam_proh) ? $clubStatData->gam_proh : NULL;
            $this->setGames();
            $this->points = isset($clubStatData->body) ? $clubStatData->body : NULL;
        }
    }
    
    private function readClubStats() {
        return $this->database->query('SELECT * from tymy_stat_vse_new(?,?,?,?)', (int) $this->matchTypeId, (int) $this->competition->getId(), (int) $this->seasonYear, (int) $this->club->getId())->fetch();
    }
    
    protected function readRankingPosition() {
        return $this->database->query('WITH clubTable AS (SELECT id, ROW_NUMBER() OVER () FROM tymy_stat_vse_jednotlive(?,?,?,?)) SELECT row_number FROM clubTable WHERE id = ?', (int) $this->matchTypeId, (int) $this->competition->getId(), (int) $this->seasonYear, 0, (int) $this->club->getId())->fetchField();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
