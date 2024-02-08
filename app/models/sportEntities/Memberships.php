<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\SportEntity\Competitions;
use App\Model\Entity\SportEntity\Clubs;

/**
 * Description of Matches
 *
 * @author inComputer
 */
class Memberships extends BasicEntity {

    private
            $id,
            $club,
            $competition,
            $dateSince,
            $dateUntil,
            $descriptions;

    public function setId($id) {
        $this->id = $id;
    }

    public function setCompetition(Competitions $competition) {
        $this->competition = $competition;
    }

    public function setClub(Clubs $clubs) {
        $this->club = $clubs;
    }

    public function setDateSince($dateSince) {
        $this->dateSince = $dateSince;
    }

    public function setDateUntil($dateUntil) {
        $this->dateUntil = $dateUntil;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function getId() {
        return $this->id;
    }

    public function getCompetition() {
        return $this->competition;
    }

    public function getClub() {
        return $this->club;
    }

    public function getDateSince() {
        return $this->dateSince;
    }

    public function getDateUntil() {
        return $this->dateUntil;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

    protected function setMembership($membershipData) {
        if ($membershipData) {
            $this->id = $membershipData->id_pusobeni ?? NULL;
            $competition = new Competitions($this->database);
            $competition->setId($membershipData->id_soutez ?? NULL);
            $competition->setName($membershipData->jmeno ?? NULL);
            $this->competition = $competition;
            $club = new Clubs($this->database);
            $club->setId($membershipData->id_klub ?? NULL);
            $club->setName($membershipData->nazev ?? NULL);
            $this->club = $club;
            $this->dateSince = $membershipData->datum_od ?? $membershipData->datum_od != '' ?? NULL;
            $this->dateUntil = ($membershipData->datum_do && $membershipData->datum_do != '') ? $membershipData->datum_do : NULL;
            $this->descriptions = $membershipData->pusobeni_info ?? $membershipData->pusobeni_info != '' ?? NULL;
        }
    }

    public function insertMembership($membershipData) {
        try {
            $this->setMembership($membershipData);
            $this->createMembership();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateMembership($id, $membershipData) {
        try {
            $this->setMembership($membershipData);
            $this->setId($id);
            $this->editMembership();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteMembership() {
        try {
            $this->eraseMembership();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcMembership() {
        try {
            $values = $this->readMembership();
            $this->setMembership($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_pusobeni VALUES (?,?,?,?,?,?,?,?,?,?)', $this->club->getId(), $this->competition->getId(), $this->dateSince, $this->dateUntil, NULL, $this->descriptions, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_pusobeni VALUES (?,?,?,?,?,?,?,?,?,?)', $this->club->getId(), $this->competition->getId(), $this->dateSince, $this->dateUntil, $this->id, $this->descriptions, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_pusobeni VALUES (?,?,?,?,?,?,?,?,?,?)', $this->club->getId(), $this->competition->getId(), $this->dateSince, $this->dateUntil, $this->id, $this->descriptions, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createMembership() {
        return $this->database->query('SELECT * FROM pusobeni_vkladani(?,?,?,?,?)', (int) $this->club->getId(), (int) $this->competition->getId(), $this->dateSince, $this->dateUntil, $this->descriptions
                )->fetch();
    }

    private function editMembership() {
        return $this->database->query('SELECT * FROM pusobeni_uprava(?,?,?,?,?,?)', (int) $this->id, (int) $this->club->getId(), (int) $this->competition->getId(), $this->dateSince, $this->dateUntil, $this->descriptions
                )->fetch();
    }

    private function eraseMembership() {
        return $this->database->query('DELETE FROM pusobeni WHERE id_pusobeni = ?', (int) $this->id)->fetch();
    }

    private function readMembership() {
        return $this->database->query('SELECT * FROM pusobeni NATURAL JOIN klub NATURAL JOIN soutez WHERE id_pusobeni = ?', (int) $this->id)->fetch();
    }

}
