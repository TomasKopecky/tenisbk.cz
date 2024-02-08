<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

/**
 * Description of MatchList
 *
 * @author inComputer
 */
class MatchesTable extends Matches {

    private
            $round,
            $clubHome,
            $clubVisitors,
            $playerHome1,
            $playerHome2,
            $playerVisitors1,
            $playerVisitors2,
            $results,
            $competition;

    public function setRound(Rounds $round) {
        $this->round = $round;
    }

    public function setClubHome(Clubs $clubHome) {
        $this->clubHome = $clubHome;
    }

    public function setClubVisitors(Clubs $clubVisitors) {
        $this->clubVisitors = $clubVisitors;
    }

    public function setPlayerHome1(Players $playerHome1) {
        $this->playerHome1 = $playerHome1;
    }

    public function setPlayerHome2($playerHome2) {
        $this->playerHome2 = $playerHome2;
    }

    public function setPlayerVisitors1(Players $playerVisitors1) {
        $this->playerVisitors1 = $playerVisitors1;
    }

    public function setPlayerVisitors2($playerVisitors2) {
        $this->playerVisitors2 = $playerVisitors2;
    }

    public function setResult(Results $result) {
        $this->results = $result;
    }

    public function setCompetition(Competitions $competition) {
        $this->competition = $competition;
    }

    protected function setMatchesTable($matchData) {
        if ($matchData) {
            isset($matchData->id_zapas) ? $this->setId($matchData->id_zapas) : null;
            $this->setDate($matchData->datum);
            isset($matchData->rocnik) ? $this->setSeasonYear($matchData->rocnik) : null;
            $round = new Rounds($this->database);
            isset($matchData->kolo) ? $round->setNumber($matchData->kolo) : null;
            $this->setRound($round);
            isset($matchData->typ_zapasu) ? $this->setMatchTypeId($matchData->typ_zapasu) : null;
            $clubHome = new Clubs($this->database);
            isset($matchData->klub_domaci) ? $clubHome->setId($matchData->klub_domaci) : null;
            isset($matchData->klub_d_naz) ? $clubHome->setName($matchData->klub_d_naz) : null;
            isset($matchData->klub_d_slug) ? $clubHome->setSlug($matchData->klub_d_slug) : null;
            $this->setClubHome($clubHome);
            $clubVisitors = new Clubs($this->database);
            isset($matchData->klub_hoste) ? $clubVisitors->setId($matchData->klub_hoste) : null;
            isset($matchData->klub_h_naz) ? $clubVisitors->setName($matchData->klub_h_naz) : null;
            isset($matchData->klub_h_slug) ? $clubVisitors->setSlug($matchData->klub_h_slug) : null;
            $this->setClubVisitors($clubVisitors);
            $playerHome1 = new Players($this->database);
            isset($matchData->hrac1_domaci) ? $playerHome1->setId($matchData->hrac1_domaci) : null;
            isset($matchData->hrac1_domaci_slug) ? $playerHome1->setSlug($matchData->hrac1_domaci_slug) : null;
            isset($matchData->hrac_d_1_jm) ? $playerHome1->setName($matchData->hrac_d_1_jm) : null;
            isset($matchData->hrac_d_1_pohlavi) ? $playerHome1->setSex($matchData->hrac_d_1_pohlavi) : null;
            $this->setPlayerHome1($playerHome1);
            $playerHome2 = new Players($this->database);
            isset($matchData->hrac2_domaci) ? $playerHome2->setId($matchData->hrac2_domaci) : null;
            isset($matchData->hrac2_domaci_slug) ? $playerHome2->setSlug($matchData->hrac2_domaci_slug) : null;
            isset($matchData->hrac_d_2_jm) ? $playerHome2->setName($matchData->hrac_d_2_jm) : null;
            isset($matchData->hrac_d_2_pohlavi) ? $playerHome2->setSex($matchData->hrac_d_2_pohlavi) : null;
            $this->setPlayerHome2($playerHome2);
            $playerVisitors1 = new Players($this->database);
            isset($matchData->hrac1_hoste) ? $playerVisitors1->setId($matchData->hrac1_hoste) : null;
            isset($matchData->hrac1_hoste_slug) ? $playerVisitors1->setSlug($matchData->hrac1_hoste_slug) : null;
            isset($matchData->hrac_h_1_jm) ? $playerVisitors1->setName($matchData->hrac_h_1_jm) : null;
            isset($matchData->hrac_h_1_pohlavi) ? $playerVisitors1->setSex($matchData->hrac_h_1_pohlavi) : null;
            $this->setPlayerVisitors1($playerVisitors1);
            $playerVisitors2 = new Players($this->database);
            isset($matchData->hrac2_hoste) ? $playerVisitors2->setId($matchData->hrac2_hoste) : null;
            isset($matchData->hrac2_hoste_slug) ? $playerVisitors2->setSlug($matchData->hrac2_hoste_slug) : null;
            isset($matchData->hrac_h_2_jm) ? $playerVisitors2->setName($matchData->hrac_h_2_jm) : null;
            isset($matchData->hrac_h_2_pohlavi) ? $playerVisitors2->setSex($matchData->hrac_h_2_pohlavi) : null;
            $this->setPlayerVisitors2($playerVisitors2);
            $play = new Plays($this->database);
            isset($matchData->id_utkani) ? $play->setId($matchData->id_utkani) : null;
            $this->setPlay($play);
            $result = new Results($this->database);
            isset($matchData->sety_domaci) ? $result->setSetsHome($matchData->sety_domaci) : null;
            isset($matchData->sety_hoste) ? $result->setSetsVisitors($matchData->sety_hoste) : null;
            isset($matchData->set1_domaci) ? $result->setSet1Home($matchData->set1_domaci) : null;
            isset($matchData->set2_domaci) ? $result->setSet2Home($matchData->set2_domaci) : null;
            isset($matchData->set3_domaci) ? $result->setSet3Home($matchData->set3_domaci) : null;
            isset($matchData->set1_hoste) ? $result->setSet1Visitors($matchData->set1_hoste) : null;
            isset($matchData->set2_hoste) ? $result->setSet2Visitors($matchData->set2_hoste) : null;
            isset($matchData->set3_hoste) ? $result->setSet3Visitors($matchData->set3_hoste) : null;
            isset($matchData->tb1_domaci) ? $result->setTb1Home($matchData->tb1_domaci) : null;
            isset($matchData->tb2_domaci) ? $result->setTb2Home($matchData->tb2_domaci) : null;
            isset($matchData->tb3_domaci) ? $result->setTb3Home($matchData->tb3_domaci) : null;
            isset($matchData->tb1_hoste) ? $result->setTb1Visitors($matchData->tb1_hoste) : null;
            isset($matchData->tb2_hoste) ? $result->setTb2Visitors($matchData->tb2_hoste) : null;
            isset($matchData->tb3_hoste) ? $result->setTb3Visitors($matchData->tb3_hoste) : null;
            $this->setResult($result);
            isset($matchData->vyhra_domaci) ? $this->setWinHome($matchData->vyhra_domaci) : null;
            isset($matchData->vyhra_hoste) ? $this->setWinVisitors($matchData->vyhra_hoste) : null;
            $this->setLossDefaultHome($matchData->zapas_kontumace_domaci);
            $this->setLossDefaultVisitors($matchData->zapas_kontumace_hoste);
            $this->setRetireHome($matchData->skrec_domaci);
            $this->setRetireVisitors($matchData->skrec_hoste);
            isset($matchData->zapas_muzi_poradi) ? $this->setMatchMenOrder($matchData->zapas_muzi_poradi) : null;
            isset($matchData->zapas_info) && $matchData->zapas_info != '' ? $this->setDescriptions($matchData->zapas_info) : null;
        }
    }

    private function readMatchesTable() {
        return $this->database->query('SELECT * FROM tymy_zapasy(?,?,?,?,?,?,?,?)', $this->getSeasonYear() ? (int) $this->getSeasonYear() : 0, !is_null($this->getRound()) ? (int) $this->getRound()->getNumber() : 0, !is_null($this->getCompetition()) ? (int) $this->getCompetition()->getId() : 0, 0, !is_null($this->getPlay()) ? (int) $this->getPlay()->getId() : 0, !is_null($this->getId()) ? (int) $this->getId() : 0, !is_null($this->getPlayer()) ? (int) $this->getPlayer()->getId() : 0, $this->getMatchTypeId() ? (int) $this->getMatchTypeId() : 0)->fetch();
    }

    public function calcMatchesTable() {
        try {
            $this->setMatchesTable($this->readMatchesTable());
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function insertMatchesTable($idPlay, $matchType, $setsHome, $setsVisitors, $winHome, $winVisitors, $matchData) {
        try {
            $this->setMatchesTable($matchData);
            $play = new Plays($this->database);
            $play->setId($idPlay);
            $this->setPlay($play);
            $this->matchTypeId = $matchType;
            $this->setSetsHome($setsHome);
            $this->setSetsVisitors($setsVisitors);
            $this->setWinHome($winHome);
            $this->setWinVisitors($winVisitors);
            $this->createMatchesTable();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateMatchesTable($id, $setsHome, $setsVisitors, $winHome, $winVisitors, $matchData) {
        try {
            $this->setMatchesTable($matchData);
            $this->setId($id);
            $this->setSetsHome($setsHome);
            $this->setSetsVisitors($setsVisitors);
            $this->setWinHome($winHome);
            $this->setWinVisitors($winVisitors);
            $this->editMatchesTable();
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    public function logInsert() {
        $this->logInsertMatch();
        $this->logInsertMatchMembership();
        $this->logInsertMatchResult();
    }
    
    public function logUpdate() {
        $this->logUpdateMatch();
        $this->logUpdateMatchMembership();
        $this->logUpdateMatchResult();
    }
    
    public function logDelete() {
        $this->logDeleteMatch();
        $this->logDeleteMatchMembership();
        $this->logDeleteMatchResult();
    }
    
    public function logInsertMatchResult() {
        return $this->database->query('INSERT INTO log.log_vysledek VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->getId(), (int) !is_null($this->results->getSet1Home()) ? $this->results->getSet1Home() : 0, (int) !is_null($this->results->getSet2Home()) ? $this->results->getSet2Home() : 0, (int) !is_null($this->results->getSet3Home()) ? $this->results->getSet3Home() : 0, (int) !is_null($this->results->getSet1Visitors()) ? $this->results->getSet1Visitors() : 0, (int) !is_null($this->results->getSet2Visitors()) ? $this->results->getSet2Visitors() : 0, (int) !is_null($this->results->getSet3Visitors()) ? $this->results->getSet3Visitors() : 0, (int) !is_null($this->results->getTb1Home()) ? $this->results->getTb1Home() : 0, (int) !is_null($this->results->getTb2Home()) ? $this->results->getTb2Home() : 0, (int) !is_null($this->results->getTb3Home()) ? $this->results->getTb3Home() : 0, (int) !is_null($this->results->getTb1Visitors()) ? $this->results->getTb1Visitors() : 0, (int) !is_null($this->results->getTb2Visitors()) ? $this->results->getTb2Visitors() : 0, (int) !is_null($this->results->getTb3Visitors()) ? $this->results->getTb3Visitors() : 0, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdateMatchResult() {
        return $this->database->query('INSERT INTO log.log_vysledek VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->getId(), (int) !is_null($this->results->getSet1Home()) ? $this->results->getSet1Home() : 0, (int) !is_null($this->results->getSet2Home()) ? $this->results->getSet2Home() : 0, (int) !is_null($this->results->getSet3Home()) ? $this->results->getSet3Home() : 0, (int) !is_null($this->results->getSet1Visitors()) ? $this->results->getSet1Visitors() : 0, (int) !is_null($this->results->getSet2Visitors()) ? $this->results->getSet2Visitors() : 0, (int) !is_null($this->results->getSet3Visitors()) ? $this->results->getSet3Visitors() : 0, (int) !is_null($this->results->getTb1Home()) ? $this->results->getTb1Home() : 0, (int) !is_null($this->results->getTb2Home()) ? $this->results->getTb2Home() : 0, (int) !is_null($this->results->getTb3Home()) ? $this->results->getTb3Home() : 0, (int) !is_null($this->results->getTb1Visitors()) ? $this->results->getTb1Visitors() : 0, (int) !is_null($this->results->getTb2Visitors()) ? $this->results->getTb2Visitors() : 0, (int) !is_null($this->results->getTb3Visitors()) ? $this->results->getTb3Visitors() : 0, FALSE, FALSE, TRUE, FALSE)->fetch();
   }
    
    public function logDeleteMatchResult() {
        return $this->database->query('INSERT INTO log.log_vysledek VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->getId(), (int) !is_null($this->results->getSet1Home()) ? $this->results->getSet1Home() : 0, (int) !is_null($this->results->getSet2Home()) ? $this->results->getSet2Home() : 0, (int) !is_null($this->results->getSet3Home()) ? $this->results->getSet3Home() : 0, (int) !is_null($this->results->getSet1Visitors()) ? $this->results->getSet1Visitors() : 0, (int) !is_null($this->results->getSet2Visitors()) ? $this->results->getSet2Visitors() : 0, (int) !is_null($this->results->getSet3Visitors()) ? $this->results->getSet3Visitors() : 0, (int) !is_null($this->results->getTb1Home()) ? $this->results->getTb1Home() : 0, (int) !is_null($this->results->getTb2Home()) ? $this->results->getTb2Home() : 0, (int) !is_null($this->results->getTb3Home()) ? $this->results->getTb3Home() : 0, (int) !is_null($this->results->getTb1Visitors()) ? $this->results->getTb1Visitors() : 0, (int) !is_null($this->results->getTb2Visitors()) ? $this->results->getTb2Visitors() : 0, (int) !is_null($this->results->getTb3Visitors()) ? $this->results->getTb3Visitors() : 0, FALSE, FALSE, FALSE, TRUE)->fetch();
   }
    
    public function logInsertMatch() {
        return $this->database->query('INSERT INTO log.log_zapas VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', NULL, $this->getPlay()->getId(), $this->matchTypeId, $this->getDate(), $this->getSetsHome(), $this->getSetsVisitors(), $this->getWinHome(), $this->getWinVisitors(), $this->getRetireHome(), $this->getRetireVisitors(), $this->getDescriptions(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdateMatch() {
        return $this->database->query('INSERT INTO log.log_zapas VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $this->getId(), $this->getPlay()->getId(), $this->matchTypeId, $this->getDate(), $this->getSetsHome(), $this->getSetsVisitors(), $this->getWinHome(), $this->getWinVisitors(), $this->getRetireHome(), $this->getRetireVisitors(), $this->getDescriptions(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDeleteMatch() {
        return $this->database->query('INSERT INTO log.log_zapas VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $this->getId(), $this->getPlay()->getId(), $this->matchTypeId, $this->getDate(), $this->getSetsHome(), $this->getSetsVisitors(), $this->getWinHome(), $this->getWinVisitors(), $this->getRetireHome(), $this->getRetireVisitors(), $this->getDescriptions(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), FALSE, FALSE, FALSE, TRUE)->fetch();
    }
    
    public function logInsertMatchMembership() {
        return $this->database->query('INSERT INTO log.log_ucast_na_zapasu VALUES (?,?,?,?,?,?,?,?,?)', NULL, $this->playerHome1->getId(), $this->playerHome2->getId(), $this->playerVisitors1->getId(), $this->playerVisitors2->getId(), FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdateMatchMembership() {
        return $this->database->query('INSERT INTO log.log_ucast_na_zapasu VALUES (?,?,?,?,?,?,?,?,?)', $this->getId(), $this->playerHome1->getId(), $this->playerHome2->getId(), $this->playerVisitors1->getId(), $this->playerVisitors2->getId(), FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDeleteMatchMembership() {
        return $this->database->query('INSERT INTO log.log_ucast_na_zapasu VALUES (?,?,?,?,?,?,?,?,?)', $this->getId(), $this->playerHome1->getId(), $this->playerHome2->getId(), $this->playerVisitors1->getId(), $this->playerVisitors2->getId(), FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createMatchesTable() {
        return $this->database->query(
                        'SELECT * FROM zapasy_vkladani(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->getPlay()->getId(), (int) $this->getMatchTypeId(), !is_null($this->getPlayerHome1()->getId()) ? (int) $this->getPlayerHome1()->getId() : null, !is_null($this->getPlayerHome2()->getId()) ? (int) $this->getPlayerHome2()->getId() : null, !is_null($this->getPlayerVisitors1()->getId()) ? (int) $this->getPlayerVisitors1()->getId() : null, !is_null($this->getPlayerVisitors2()->getId()) ? (int) $this->getPlayerVisitors2()->getId() : null, $this->getDate(), (int) $this->getSetsHome(), (int) $this->getSetsVisitors(), $this->getWinHome(), (int) $this->getWinVisitors(), (int) $this->getResults()->getSet1Home(), (int) $this->getResults()->getSet2Home(), (int) $this->getResults()->getSet3Home(), (int) $this->getResults()->getSet1Visitors(), (int) $this->getResults()->getSet2Visitors(), (int) $this->getResults()->getSet3Visitors(), (int) $this->getResults()->getTb1Home(), (int) $this->getResults()->getTb2Home(), (int) $this->getResults()->getTb3Home(), (int) $this->getResults()->getTb1Visitors(), (int) $this->getResults()->getTb2Visitors(), (int) $this->getResults()->getTb3Visitors(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), $this->getRetireHome(), $this->getRetireVisitors(), $this->getMatchMenOrder(), $this->getDescriptions()
                )->fetch();
    }

    private function editMatchesTable() {
        return $this->database->query(
                        'SELECT * FROM zapasy_uprava(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->getId(), (int) $this->getMatchTypeId(), !is_null($this->getPlayerHome1()->getId()) ? (int) $this->getPlayerHome1()->getId() : null, !is_null($this->getPlayerHome2()->getId()) ? (int) $this->getPlayerHome2()->getId() : null, !is_null($this->getPlayerVisitors1()->getId()) ? (int) $this->getPlayerVisitors1()->getId() : null, !is_null($this->getPlayerVisitors2()->getId()) ? (int) $this->getPlayerVisitors2()->getId() : null, $this->getDate(), (int) $this->getSetsHome(), (int) $this->getSetsVisitors(), $this->getWinHome(), (int) $this->getWinVisitors(), (int) $this->getResults()->getSet1Home(), (int) $this->getResults()->getSet2Home(), (int) $this->getResults()->getSet3Home(), (int) $this->getResults()->getSet1Visitors(), (int) $this->getResults()->getSet2Visitors(), (int) $this->getResults()->getSet3Visitors(), (int) $this->getResults()->getTb1Home(), (int) $this->getResults()->getTb2Home(), (int) $this->getResults()->getTb3Home(), (int) $this->getResults()->getTb1Visitors(), (int) $this->getResults()->getTb2Visitors(), (int) $this->getResults()->getTb3Visitors(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), $this->getRetireHome(), $this->getRetireVisitors(), $this->getMatchMenOrder(), $this->getDescriptions()
                )->fetch();
    }

    public function getRound() {
        return $this->round;
    }

    public function getClubHome() {
        return $this->clubHome;
    }

    public function getClubVisitors() {
        return $this->clubVisitors;
    }

    public function getPlayerHome1() {
        return $this->playerHome1;
    }

    public function getPlayerHome2() {
        return $this->playerHome2;
    }

    public function getPlayerVisitors1() {
        return $this->playerVisitors1;
    }

    public function getPlayerVisitors2() {
        return $this->playerVisitors2;
    }

    public function getResults() {
        return $this->results;
    }

    public function getCompetition() {
        return $this->competition;
    }

}
