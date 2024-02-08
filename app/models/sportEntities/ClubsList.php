<?php

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Clubs;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class ClubsList extends Clubs {

    private $clubsList = array();

    private function setClubsList($clubsData) {
        foreach ($clubsData as $club) {
            $newClubs = new Clubs($this->database);
            $newClubs->setClub($club);
            $this->clubsList[] = $newClubs;
        }
    }

    public function calcClubsList() {
        try {
            $values = $this->readClubsList();
            $this->setClubsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function calcClubsListByYearAndComp($compId, $season) {
        try {
            $values = $this->readClubsListByYearAndComp($compId, $season);
            $this->setClubsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function calcClubsByPlayer() {
        try {
            $values = $this->readClubByPlayer();
            $this->setClubsList($values);
        } catch (\Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function getClubsList() {
        return $this->clubsList;
    }

    private function readClubsList() {
        return $this->database->query('SELECT * FROM klub ORDER BY nazev')->fetchAll();
    }

    private function readClubsListByYearAndComp($compId, $season) {
        return $this->database->query("SELECT DISTINCT id_klub, nazev FROM pusobeni NATURAL JOIN klub WHERE id_soutez = ? AND CAST('1.1.'||? AS DATE) BETWEEN datum_od AND CASE WHEN datum_do IS NULL THEN '1.1.9999' ELSE datum_do END", (int) $compId, $season)->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
