<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of Ranking
 *
 * @author inComputer
 */
class Competitions extends BasicEntity {

    private
            $id,
            $name,
            $admin,
            $descriptions,
            $activeYears;

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setAdmin($admin) {
        $this->admin = $admin;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    protected function setCompetition($competitionsData) {
        if ($competitionsData) {
            $this->id = $competitionsData->id_soutez ?? NULL;
            $this->name = $competitionsData->jmeno ?? NULL;
            $this->admin = ($competitionsData->spravce_souteze && $competitionsData->spravce_souteze != '') ? $competitionsData->spravce_souteze : NULL;
            $this->descriptions = ($competitionsData->soutez_info && $competitionsData->soutez_info != '') ? $competitionsData->soutez_info : NULL;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getAdmin() {
        return $this->admin;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getCurrentCompetitions() {
        try {
            return $this->readCurrentCompetitions();
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    public function getActiveYears() {
        return $this->activeYears;
    }

    public function calcActiveYears() {
        try {
            $years = $this->readCompetitionsActiveYears();
            $activeYears = array();
            $activeYears[] = 0;
            foreach ($years as $year) {
                $activeYears[] = $year->rocnik;
            }
            $this->activeYears = $activeYears;
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function calcCompetitions() {
        try {
            $values = $this->readCompetitions();
            $this->setCompetition($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function insertCompetition($competitionData) {
        try {
            $this->setCompetition($competitionData);
            $this->createCompetition();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateCompetition($id, $competitionData) {
        try {
            $this->setCompetition($competitionData);
            $this->setId($id);
            $this->editCompetition();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteCompetition() {
        try {
            $this->eraseCompetition();
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_soutez VALUES (?,?,?,?,?,?,?,?)', NULL, $this->name, $this->admin, $this->descriptions, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_soutez VALUES (?,?,?,?,?,?,?,?)', $this->id, $this->name, $this->admin, $this->descriptions, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_soutez VALUES (?,?,?,?,?,?,?,?)', $this->id, $this->name, $this->admin, $this->descriptions, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function readCompetitionsActiveYears() {
        return $this->database->query('SELECT distinct rocnik FROM utkani NATURAL JOIN ucast_na_utkani NATURAL JOIN pusobeni')->fetchAll();
    }

    private function readCurrentCompetitions() {
        return $this->database->query('SELECT distinct id_soutez FROM pusobeni WHERE ? between datum_od and case when datum_do is null then ? else datum_do end', $this->date, $this->date)->fetchAll();
    }

    private function readCompetitions() {
        return $this->database->query('SELECT * FROM soutez WHERE id_soutez = ? ORDER BY jmeno', (int) $this->id)->fetch();
    }

    private function createCompetition() {
        return $this->database->query('SELECT * FROM souteze_vkladani(?,?,?)', $this->name, $this->admin, $this->descriptions)->fetch();
    }

    private function editCompetition() {
        return $this->database->query('SELECT * FROM souteze_uprava(?,?,?,?)', (int) $this->id, $this->name, $this->admin, $this->descriptions)->fetch();
    }

    private function eraseCompetition() {
        return $this->database->query('DELETE FROM soutez WHERE id_soutez = ?', (int) $this->id)->fetch();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
