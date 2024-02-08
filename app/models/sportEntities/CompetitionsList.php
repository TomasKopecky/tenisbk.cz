<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Competitions;

/**
 * Description of Ranking
 *
 * @author inComputer
 */
class CompetitionsList extends Competitions {

    private $competitionsList = array(),
            $date,
            $club;

    private function setCompetitionsList($competitionsData) {
        foreach ($competitionsData as $competition) {
            $newCompetition = new Competitions($this->database);
            $newCompetition->setCompetition($competition);
            $this->competitionsList[] = $newCompetition;
        }
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setClub(Clubs $club) {
        $this->club = $club;
    }

    public function getCompetitionsList() {
        return $this->competitionsList;
    }

    public function calcCompetitionsList() {
        try {
            $values = $this->readCompetitionsList();
            $this->setCompetitionsList($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
    }}
        
            public function calcCurrentCompetitionsList() {
        try {
            return $this->readCurrentCompetitionsList();
        } catch (Nette\Neon\Exception $e) {
            return $e;
            }}

    public function getClubCompetitions() {
        try {
            $values = $this->readClubCompetitions();
            $this->setCompetitionsList($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    public function geCompActiveYears() {
        try {
            $values = $this->readCompActiveYears();
            return $values;
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    private function readCompActiveYears() {
        return $this->database->query('SELECT DISTINCT date_part("year", datum_od) AS rocnik FROM pusobeni')->fetchAll();
    }

    private function readClubCompetitions() {
        return $this->database->query('SELECT DISTINCT (id_soutez), jmeno, spravce_souteze, soutez_info FROM pusobeni P NATURAL JOIN soutez S WHERE id_klub = ? ORDER BY id_soutez', $this->club->getId())->fetchAll();
    }
    
        private function readCurrentCompetitionsList() {
        return $this->database->query('SELECT distinct id_soutez FROM pusobeni WHERE ? between datum_od and case when datum_do is null then ? else datum_do end', $this->date, $this->date)->fetchAll();
    }

    private function readCompetitionsList() {
        return $this->database->query('SELECT * FROM soutez ORDER BY jmeno')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
