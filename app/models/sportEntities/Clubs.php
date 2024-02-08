<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of Club
 *
 * @author inComputer
 */
class Clubs extends BasicEntity {

    private
            $id,
            $name,
            $shortcut,
            $nick,
            $address,
            $slug,
            $descriptions,
            $contactPerson1Name,
            $contactPerson1Email,
            $contactPerson1Phone,
            $contactPerson2Name,
            $contactPerson2Email,
            $contactPerson2Phone,
            $contactPerson3Name,
            $contactPerson3Email,
            $contactPerson3Phone,
            $players,
            $activeYears;

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setNick($nick) {
        $this->nick = $nick;
    }

    public function setShortcut($shortcut) {
        $this->shortcut = $shortcut;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }
    
    public function setContactPerson1Name($name) {
        $this->contactPerson1Name = $name;
    }
    
    public function setContactPerson1Email($email) {
        $this->contactPerson1Email = $email;
    }
    
    public function setContactPerson1Phone($phone) {
        $this->contactPerson1Phone = $phone;
    }
    
    public function setContactPerson2Name($name) {
        $this->contactPerson2Name = $name;
    }
    
    public function setContactPerson2Email($email) {
        $this->contactPerson2Email = $email;
    }
    
    public function setContactPerson2Phone($phone) {
        $this->contactPerson1Phone = $phone;
    }
    
    public function setContactPerson3Name($name) {
        $this->contactPersonName = $name;
    }
    
    public function setContactPerson3Email($email) {
        $this->contactPerson3Email = $email;
    }
    
    public function setContactPerson3Phone($phone) {
        $this->contactPerson3Phone = $phone;
    }

    public function setClub($clubData) {
        $this->id = isset($clubData->id_klub) ? $clubData->id_klub : (isset($clubData->id) ? $clubData->id : NULL);
        $this->name = isset($clubData->nazev) ? $clubData->nazev : NULL;
        $this->shortcut = isset($clubData->zkratka) ? $clubData->zkratka : NULL;
        $this->nick = isset($clubData->nick) ? $clubData->nick : NULL;
        $this->address = isset($clubData->adresa) && $clubData->adresa != '' ? $clubData->adresa : NULL;
        $this->slug = isset($clubData->klub_slug) ? $clubData->klub_slug : NULL;
        $this->descriptions = isset($clubData->klub_info) && $clubData->klub_info != '' ? $clubData->klub_info : NULL;
        $this->contactPerson1Name = isset($clubData->osoba_kontakt_1_jmeno) && $clubData->osoba_kontakt_1_jmeno != '' ? $clubData->osoba_kontakt_1_jmeno : NULL;
        $this->contactPerson1Email = isset($clubData->osoba_kontakt_1_email) && $clubData->osoba_kontakt_1_email != '' ? $clubData->osoba_kontakt_1_email : NULL;
        $this->contactPerson1Phone = isset($clubData->osoba_kontakt_1_telefon) && $clubData->osoba_kontakt_1_telefon != '' ? $clubData->osoba_kontakt_1_telefon : NULL;
        $this->contactPerson2Name = isset($clubData->osoba_kontakt_2_jmeno) && $clubData->osoba_kontakt_2_jmeno != '' ? $clubData->osoba_kontakt_2_jmeno : NULL;
        $this->contactPerson2Email = isset($clubData->osoba_kontakt_2_email) && $clubData->osoba_kontakt_2_email != '' ? $clubData->osoba_kontakt_2_email : NULL;
        $this->contactPerson2Phone = isset($clubData->osoba_kontakt_2_telefon) && $clubData->osoba_kontakt_2_telefon != '' ? $clubData->osoba_kontakt_2_telefon : NULL;
        $this->contactPerson3Name = isset($clubData->osoba_kontakt_3_jmeno) && $clubData->osoba_kontakt_3_jmeno != '' ? $clubData->osoba_kontakt_3_jmeno : NULL;
        $this->contactPerson3Email = isset($clubData->osoba_kontakt_3_email) && $clubData->osoba_kontakt_3_email != '' ? $clubData->osoba_kontakt_3_email : NULL;
        $this->contactPerson3Phone = isset($clubData->osoba_kontakt_3_telefon) && $clubData->osoba_kontakt_3_telefon != '' ? $clubData->osoba_kontakt_3_telefon : NULL;
    }

    public function setPlayer(Players $players) {
        $this->players = $players;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getShortcut() {
        return $this->shortcut;
    }

    public function getNick() {
        return $this->nick;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getContactPerson1Name(){
        return $this->contactPerson1Name;
    }
    
    public function getContactPerson1Email(){
        return $this->contactPerson1Email;
    }
    
    public function getContactPerson1Phone(){
        return $this->contactPerson1Phone;
    }
    
    public function getContactPerson2Name(){
        return $this->contactPerson2Name;
    }
    
    public function getContactPerson2Email(){
        return $this->contactPerson2Email;
    }
    
    public function getContactPerson2Phone(){
        return $this->contactPerson2Phone;
    }
    
    public function getContactPerson3Name(){
        return $this->contactPerson3Name;
    }
    
    public function getContactPerson3Email(){
        return $this->contactPerson3Email;
    }
    
    public function getContactPerson3Phone(){
        return $this->contactPerson3Phone;
    }
    
    public function getActiveYears() {
        return $this->activeYears;
    }

    public function calcClub() {
        try {
            if (!empty($this->getId())) {
                $values = $this->readClubById();
            } else {
                $values = $this->readClubBySlug();
            }
            $this->setClub($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function calcClubByPlayer() {
        try {
            $values = $this->readClubByPlayer();
            $this->setClub($values);
        } catch (\Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function calcActiveYears() {
        try {
            $years = $this->readClubActiveYears();
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

    public function insertClub($clubData) {
        try {
            $this->setClub($clubData);
            $this->createClub();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateClub($id, $clubData) {
        try {
            $this->setClub($clubData);
            $this->setId($id);
            $this->editClub();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteClub() {
        try {
            $this->eraseClub();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_klub VALUES (?,?,?,?,?,?,?,?,?,?)', NULL, $this->name, $this->shortcut, $this->nick, $this->address, $this->descriptions, FALSE, TRUE, FALSE, FALSE)->fetch();
    }

    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_klub VALUES (?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->shortcut, $this->nick, $this->address, $this->descriptions, FALSE, FALSE, TRUE, FALSE)->fetch();
    }

    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_klub VALUES (?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->shortcut, $this->nick, $this->address, $this->descriptions, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function editClub() {
        return $this->database->query('SELECT * FROM kluby_uprava(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->shortcut, $this->nick, $this->address, $this->contactPerson1Name, $this->contactPerson1Email, $this->contactPerson1Phone, $this->contactPerson2Name, $this->contactPerson2Email, $this->contactPerson2Phone, $this->contactPerson3Name, $this->contactPerson3Email, $this->contactPerson3Phone, $this->descriptions)->fetch();
    }

    protected function readClubByPlayer() {
        return $this->database->query('SELECT * FROM klub NATURAL JOIN registrace WHERE id_hrac = ? AND ? between datum_od and CASE WHEN datum_do IS NULL THEN NOW() ELSE datum_do END  ORDER BY id_registrace DESC', $this->players->getId(), date('Y-m-d'))->fetchAll();
    }

    private function readClubById() {
        return $this->database->query('SELECT * FROM klub WHERE id_klub = ?', (int) $this->id)->fetch();
    }

    private function readClubBySlug() {
        return $this->database->query('SELECT * FROM klub WHERE klub_slug = ?', $this->slug)->fetch();
    }

    private function readClubActiveYears() {
        return $this->database->query('SELECT DISTINCT rocnik FROM tymy_utkani(?,?,?,?)', 0, 0, 0, $this->id)->fetchAll();
    }

    private function createClub() {
        return $this->database->query('SELECT * FROM kluby_vkladani(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $this->name, $this->shortcut, $this->nick, $this->address, $this->contactPerson1Name, $this->contactPerson1Email, $this->contactPerson1Phone, $this->contactPerson2Name, $this->contactPerson2Email, $this->contactPerson2Phone, $this->contactPerson3Name, $this->contactPerson3Email, $this->contactPerson3Phone, $this->descriptions)->fetch();
    }

    private function eraseClub() {
        return $this->database->query('DELETE FROM klub WHERE id_klub = ?', (int) $this->id)->fetch();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
