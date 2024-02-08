<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Plays;

/**
 * Description of MatchList
 *
 * @author inComputer
 */
class PlaysTable extends Plays {

    protected
            $competition,
            $clubHome,
            $clubVisitors,
            $matchesHome,
            $matchesVisitors,
            $winHome,
            $winVisitors,
            $club;

    public function setClub(Clubs $club) {
        $this->club = $club;
    }

    public function setCompetition(Competitions $competition) {
        $this->competition = $competition;
    }

    public function setClubHome(Clubs $clubHome) {
        $this->clubHome = $clubHome;
    }

    public function setClubVisitors(Clubs $clubVisitors) {
        $this->clubVisitors = $clubVisitors;
    }

    public function setMatchesHome($matchesHome) {
        $this->matchesHome = $matchesHome;
    }

    public function setMatchesVisitors($matchesVisitors) {
        $this->matchesVisitors = $matchesVisitors;
    }

    public function getClubHome() {
        return $this->clubHome;
    }

    public function getClubVisitors() {
        return $this->clubVisitors;
    }

    public function getMatchesHome() {
        return $this->matchesHome;
    }

    public function getMatchesVisitors() {
        return $this->matchesVisitors;
    }

    public function getCompetition() {
        return $this->competition;
    }

    protected function setPlaysTable($playsData) {
        if ($playsData) {
            $competition = new Competitions($this->database);
            $clubHome = new Clubs($this->database);
            $clubVisitors = new Clubs($this->database);
            $round = new Rounds($this->database);
            isset($playsData->id_soutez) ? $competition->setId($playsData->id_soutez) : NULL;
            isset($playsData->nazev_soutez) ? $competition->setName($playsData->nazev_soutez) : NULL;
            isset($playsData->klub_domaci) ? $clubHome->setId($playsData->klub_domaci) : NULL;
            isset($playsData->domaci) ? $clubHome->setName($playsData->domaci) : NULL;
            isset($playsData->domaci_slug) ? $clubHome->setSlug($playsData->domaci_slug) : NULL;
            isset($playsData->domaci_zkr) ? $clubHome->setShortcut($playsData->domaci_zkr) : NULL;
            isset($playsData->klub_hoste) ? $clubVisitors->setId($playsData->klub_hoste) : NULL;
            isset($playsData->hoste) ? $clubVisitors->setName($playsData->hoste) : NULL;
            isset($playsData->hoste_slug) ? $clubVisitors->setSlug($playsData->hoste_slug) : NULL;
            isset($playsData->hoste_zkr) ? $clubVisitors->setShortcut($playsData->hoste_zkr) : NULL;
            isset($playsData->kolo) ? $round->setNumber($playsData->kolo) : NULL;
            isset($playsData->id_utkani) ? $this->setId($playsData->id_utkani) : NULL;
            isset($playsData->rocnik) ? $this->setSeason($playsData->rocnik) : NULL;
            $this->setRound($round);
            $this->setCompetition($competition);
            isset($playsData->utkani_datum) && $playsData->utkani_datum != '' ? $this->setDate($playsData->utkani_datum) : NULL;
            /*isset($playsData->datum_nahradni) && $playsData->datum_nahradni != '' ? $this->setDateAlternative($playsData->datum_nahradni) : NULL;*/
            $this->setClubHome($clubHome);
            $this->setClubVisitors($clubVisitors);
            isset($playsData->domaci_zap) ? $this->setMatchesHome($playsData->domaci_zap) : NULL;
            isset($playsData->hoste_zap) ? $this->setMatchesVisitors($playsData->hoste_zap) : NULL;
            isset($playsData->vyh_dom) ? $this->setWinHome($playsData->vyh_dom) : NULL;
            isset($playsData->vyh_hos) ? $this->setWinVisitors($playsData->vyh_hos) : NULL;
            isset($playsData->utkani_kontumace_domaci) ? $this->setLossDefaultHome($playsData->utkani_kontumace_domaci) : NULL;
            isset($playsData->utkani_kontumace_hoste) ? $this->setLossDefaultVisitors($playsData->utkani_kontumace_hoste) : NULL;
            isset($playsData->utkani_info) && $playsData->utkani_info != '' ? $this->descriptions = $playsData->utkani_info : NULL;
        }
    }

    public function calcPlaysTable() {
        try {
            $values = $this->readPlaysTable();
            $this->setPlaysTable($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function insertPlaysTable($playData) {
        try {
            $this->setPlaysTable($playData);
            $this->createPlaysTable();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updatePlaysTable($id, $playData) {
        try {
            $this->setPlaysTable($playData);
            $this->setId($id);
            $this->editPlaysTable();
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    public function logInsert() {
        $this->logInsertPlay();
        $this->logInsertPlayMembership();
    }
    
    public function logUpdate() {
        $this->logUpdatePlay();
        $this->logUpdatePlayMembership();
    }
    
    public function logDelete() {
        $this->logDeletePlay();
        $this->logDeletePlayMembership();
    }
    
    private function logInsertPlay() {
        return $this->database->query('INSERT INTO log.log_utkani VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', NULL, $this->matchesHome, $this->matchesVisitors, $this->winHome, $this->winVisitors, $this->descriptions, $this->date, $this->lossDefaultHome, $this->lossDefaultVisitors, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    private function logInsertPlayMembership() {
        return $this->database->query('INSERT INTO log.log_ucast_na_utkani VALUES (?,?,?,?,?,?,?,?,?)', NULL, $this->clubHome->getId(), $this->clubVisitors->getId(), $this->round->getNumber(), $this->season, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    private function logUpdatePlay() {
        return $this->database->query('INSERT INTO log.log_utkani VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', $this->id, $this->matchesHome, $this->matchesVisitors, $this->winHome, $this->winVisitors, $this->descriptions, $this->date, $this->lossDefaultHome, $this->lossDefaultVisitors, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    private function logUpdatePlayMembership() {
        return $this->database->query('INSERT INTO log.log_ucast_na_utkani VALUES (?,?,?,?,?,?,?,?,?)', $this->id, $this->clubHome->getId(), $this->clubVisitors->getId(), $this->round->getNumber(), $this->season, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    private function logDeletePlay() {
        return $this->database->query('INSERT INTO log.log_utkani VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', $this->id, $this->matchesHome, $this->matchesVisitors, $this->winHome, $this->winVisitors, $this->descriptions, $this->date, $this->lossDefaultHome, $this->lossDefaultVisitors, FALSE, FALSE, FALSE, TRUE)->fetch();
    }
    
    private function logDeletePlayMembership() {
        return $this->database->query('INSERT INTO log.log_ucast_na_utkani VALUES (?,?,?,?,?,?,?,?,?)', $this->id, $this->clubHome->getId(), $this->clubVisitors->getId(), $this->round->getNumber(), $this->season, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createPlaysTable() {
        return $this->database->query('SELECT * FROM utkani_vkladani(?,?,?,?,?,?,?,?)', (int) $this->round->getNumber(), (int) $this->season, (int) $this->clubHome->getId(), (int) $this->clubVisitors->getId(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), $this->date, $this->descriptions
                )->fetch();
    }

    private function editPlaysTable() {
        return $this->database->query('SELECT * FROM utkani_uprava(?,?,?,?,?,?,?,?,?)', (int) $this->id, (int) $this->round->getNumber(), (int) $this->season, (int) $this->clubHome->getId(), (int) $this->clubVisitors->getId(), $this->getLossDefaultHome(), $this->getLossDefaultVisitors(), $this->date, $this->descriptions)->fetch();
    }

    private function readPlaysTable() {
        return $this->database->query('SELECT * FROM tymy_utkani(?,?,?,?,?)', 0, 0, 0, 0, (int) $this->getId())->fetch();
    }

}
