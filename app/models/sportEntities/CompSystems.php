<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\SportEntity\Competitions;

/**
 * Description of Matches
 *
 * @author inComputer
 */
class CompSystems extends BasicEntity {

    private
            $id,
            $competition,
            $season,
            $roundSystem,
            $descriptions;

    public function setId($id) {
        $this->id = $id;
    }

    public function setCompetition(Competitions $competition) {
        $this->competition = $competition;
    }

    public function setSeason($season) {
        $this->season = $season;
    }

    public function setRoundSystem($roundSystem) {
        $this->roundSystem = $roundSystem;
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

    public function getRoundSystem() {
        return $this->roundSystem;
    }

    public function getSeason() {
        return $this->season;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

    protected function setCompSystem($compSystemData) {
        if ($compSystemData) {
            $this->id = $compSystemData->id_soutez_system ?? NULL;
            $competition = new Competitions($this->database);
            $competition->setId($compSystemData->id_soutez ?? NULL);
            $competition->setName($compSystemData->jmeno ?? NULL);
            $this->competition = $competition;
            $this->season = isset($compSystemData->rocnik) ? $compSystemData->rocnik : NULL;
            $this->roundSystem = isset($compSystemData->system_kol) ? $compSystemData->system_kol : NULL;
            $this->descriptions = isset($compSystemData->soutez_system_info) && $compSystemData->soutez_system_info != '' ? $compSystemData->soutez_system_info : NULL;
        }
    }

    public function insertCompSystem($compSystemData) {
        try {
            $this->setCompSystem($compSystemData);
            $this->createCompSystem();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateCompSystem($id, $compSystemData) {
        try {
            $this->setCompSystem($compSystemData);
            $this->setId($id);
            $this->editCompSystem();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteCompSystem() {
        try {
            $this->eraseCompSystem();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcCompSystem() {
        try {
            $values = $this->readCompSystem();
            $this->setCompSystem($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_soutez_system VALUES (?,?,?,?,?,?,?,?)', NULL, $this->season, $this->roundSystem, $this->descriptions, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_soutez_system VALUES (?,?,?,?,?,?,?,?)', $this->id, $this->season, $this->roundSystem, $this->descriptions, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_soutez_system VALUES (?,?,?,?,?,?,?,?)', $this->id, $this->season, $this->roundSystem, $this->descriptions, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createCompSystem() {
        return $this->database->query('SELECT * FROM systemy_vkladani(?,?,?,?)', (int) $this->competition->getId(), (int) $this->season, (int) $this->roundSystem, $this->descriptions)->fetch();
    }

    private function editCompSystem() {
        return $this->database->query('SELECT * FROM systemy_uprava(?,?,?,?,?)', (int) $this->id, (int) $this->competition->getId(), (int) $this->season, (int) $this->roundSystem, $this->descriptions)->fetch();
    }

    private function eraseCompSystem() {
        return $this->database->query('DELETE FROM soutez_system WHERE id_soutez_system = ?', (int) $this->id)->fetch();
    }

    private function readCompSystem() {
        return $this->database->query('SELECT * FROM soutez_system NATURAL JOIN soutez WHERE id_soutez_system = ?', (int) $this->id)->fetch();
    }

}
