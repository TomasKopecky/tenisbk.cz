<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\BasicEntity;
use Nette\Utils\Random;
use Nette\Security\Passwords;

/**
 * Description of User
 *
 * @author inComputer
 */
class Users extends BasicEntity {

    private $id,
            $name,
            $email,
            $username,
            $password,
            $activeStatus,
            $descriptions,
            $regHash;

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setActiveStatus($activeStatus) {
        $this->activeStatus = $activeStatus;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function setRegHash($regHash) {
        $this->regHash = $regHash;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getActiveStatus() {
        return $this->activeStatus;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getRegHash() {
        return $this->regHash;
    }

    protected function setUser($userData) {
        if ($userData) {

            $this->id = isset($userData->id_uzivatel) ? $userData->id_uzivatel : NULL;
            $this->name = isset($userData->jmeno) ? $userData->jmeno : NULL;
            $this->username = isset($userData->uzivatelske_jmeno) ? $userData->uzivatelske_jmeno : NULL;
            $this->email = isset($userData->email) ? $userData->email : NULL;
            $this->password = isset($userData->heslo) ? $userData->heslo : NULL;
            $this->activeStatus = isset($userData->aktivni) ? $userData->aktivni : NULL;
            $this->descriptions = !isset($userData->uzivatel_info) ? NULL : ($userData->uzivatel_info != '' ? $userData->uzivatel_info : NULL);
            $this->regHash = !isset($userData->registracni_hash) ? NULL : ($userData->registracni_hash != '' ? $userData->registracni_hash : NULL);
        }
    }

    public function insertUser($userData) {
        try {
            $this->setUser($userData);
            $this->regHash = Random::generate(80);
            $this->createUser();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateUser($id, $userData) {
        try {
            $this->setUser($userData);
            $this->setId($id);
            $this->editUser();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function registerUser($id, $userData) {
        try {
            $this->setUser($userData);
            $this->setId($id);
            $this->password = Passwords::hash($this->password);
            $this->regUser();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteUser() {
        try {
            $this->eraseUser();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcUser() {
        try {
            $values = $this->readUser();
            $this->setUser($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_uzivatele VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->username, $this->email, $this->password, $this->descriptions, $this->activeStatus, $this->regHash, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_uzivatele VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->username, $this->email, $this->password, $this->descriptions, $this->activeStatus, $this->regHash, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_uzivatele VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->username, $this->email, $this->password, $this->descriptions, $this->activeStatus, $this->regHash, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function readUser() {
        return $this->database->query('SELECT * FROM uzivatele.uzivatele WHERE id_uzivatel = ?', (int) $this->id)->fetch();
    }

    private function createUser() {
        return $this->database->query('SELECT * FROM uzivatele.uzivatele_vkladani(?,?,?,?,?,?)', $this->name, $this->username, $this->email, (bool) $this->activeStatus, $this->descriptions, $this->regHash)->fetch();
    }

    private function editUser() {
        return $this->database->query('SELECT * FROM uzivatele.uzivatele_uprava(?,?,?,?,?,?)', (int) $this->id, $this->name, $this->username, $this->email, (bool) $this->activeStatus, $this->descriptions)->fetch();
    }

    private function regUser() {
        return $this->database->query('SELECT * FROM uzivatele.uzivatele_registrace(?,?,?,?)', (int) $this->id, $this->username, $this->email, $this->password)->fetch();
    }

    private function eraseUser() {
        return $this->database->query('DELETE FROM uzivatele.uzivatele WHERE id_uzivatel = ?', (int) $this->id)->fetch();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
