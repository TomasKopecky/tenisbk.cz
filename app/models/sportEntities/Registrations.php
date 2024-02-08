<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\SportEntity\Players;
use App\Model\Entity\SportEntity\Clubs;

/**
 * Description of Matches
 *
 * @author inComputer
 */
class Registrations extends BasicEntity {

    private
            $id,
            $player,
            $club,
            $dateSince,
            $dateUntil,
            $order,
            $descriptions,
            $activeYears;

    public function setId($id) {
        $this->id = $id;
    }

    public function setPlayer(Players $player) {
        $this->player = $player;
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
    
    public function setOrder($order){
         $this->order = $order;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function getId() {
        return $this->id;
    }

    public function getPlayer() {
        return $this->player;
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
    
    public function getOrder(){
        return $this->order;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getActiveYears() {
        return $this->activeYears;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

    protected function setRegistration($registrationData) {
        if ($registrationData) {
            $this->id = $registrationData->id_registrace ?? NULL;
            $player = new Players($this->database);
            $player->setId($registrationData->id_hrac ?? NULL);
            $player->setName($registrationData->jmeno ?? NULL);
            $player->setSex($registrationData->pohlavi ?? NULL);
            $player->setSlug($registrationData->hrac_slug ?? NULL);
            $this->player = $player;
            $club = new Clubs($this->database);
            $club->setId($registrationData->id_klub ?? NULL);
            $club->setName($registrationData->nazev ?? NULL);
            $club->setSlug($registrationData->klub_slug ?? NULL);
            $this->club = $club;
            $this->dateSince = $registrationData->datum_od ?? $registrationData->datum_od != '' ?? NULL;
            $this->dateUntil = ($registrationData->datum_do && $registrationData->datum_do != '') ? $registrationData->datum_do : NULL;
            $this->order = ($registrationData->hrac_muzi_poradi ?? NULL);
            $this->descriptions = isset($registrationData->registrace_info) && $registrationData->registrace_info != '' ? $this->descriptions = $registrationData->registrace_info : NULL;
        }
    }

    public function calcActiveYears() {
        try {
            $years = $this->readRegistrationsYears();
            $activeYears = array();
            //$activeYears[] = 0;
            foreach ($years as $year) {
                if (!is_null($year->rocnik)) {
                    $activeYears[] = $year->rocnik;
                }
            }
            $this->activeYears = $activeYears;
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function insertRegistration($registrationData) {
        try {
            $this->setRegistration($registrationData);
            $this->createRegistration();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateRegistration($id, $registrationData) {
        try {
            $this->setRegistration($registrationData);
            $this->setId($id);
            $this->editRegistration();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcRegistration() {
        try {
            $values = $this->readRegistration();
            $this->setRegistration($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function deleteRegistration() {
        try {
            $this->eraseRegistration();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    private function readRegistrationsYears() {
        return $this->database->query('SELECT DISTINCT EXTRACT(YEAR FROM datum_od) AS rocnik FROM registrace UNION SELECT DISTINCT EXTRACT(YEAR FROM datum_do) AS rocnik FROM registrace ORDER BY rocnik')->fetchAll();
    }

    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_registrace VALUES (?,?,?,?,?,?,?,?,?,?)', $this->player->getId(), $this->club->getId(), $this->dateSince, $this->dateUntil, NULL, $this->descriptions, FALSE, TRUE, FALSE, FALSE)->fetch();
    }

    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_registrace VALUES (?,?,?,?,?,?,?,?,?,?)', $this->player->getId(), $this->club->getId(), $this->dateSince, $this->dateUntil, $this->id, $this->descriptions, FALSE, FALSE, TRUE, FALSE)->fetch();
    }

    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_registrace VALUES (?,?,?,?,?,?,?,?,?,?)', $this->player->getId(), $this->club->getId(), $this->dateSince, $this->dateUntil, $this->id, $this->descriptions, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createRegistration() {
        return $this->database->query('SELECT * FROM registrace_vkladani(?,?,?,?,?,?)', (int) $this->player->getId(), (int) $this->club->getId(), $this->dateSince, $this->dateUntil, $this->order, $this->descriptions)->fetch();
    }

    private function editRegistration() {
        return $this->database->query('SELECT * FROM registrace_uprava(?,?,?,?,?,?,?)', (int) $this->id, (int) $this->player->getId(), (int) $this->club->getId(), $this->dateSince, $this->dateUntil, $this->order, $this->descriptions)->fetch();
    }

    private function eraseRegistration() {
        return $this->database->query('DELETE FROM registrace WHERE id_registrace = ?', (int) $this->id)->fetch();
    }

    private function readRegistration() {
        return $this->database->query('SELECT * FROM registrace NATURAL JOIN hrac NATURAL JOIN klub WHERE id_registrace = ?', (int) $this->id)->fetch();
    }

}
