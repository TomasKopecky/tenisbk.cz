<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\UserEntity\Users;
use App\Model\Entity\UserEntity\Events;

/**
 * Description of Logs
 *
 * @author inComputer
 */
class Logs extends BasicEntity {

    private
            $id,
            $user,
            $event,
            $username,
            $password,
            $timestamp;

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setUser(Users $user) {
        $this->user = $user;
    }

    public function setEvent(Events $event) {
        $this->event = $event;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }
    
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getUser() {
        return $this->user;
    }

    public function getEvent() {
        return $this->event;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function getPassword()
    {
        return $this->password;
    }

    public function setLog($logsData) {
        if ($logsData) {
            $user = new Users($this->database);
            isset($logsData->id_uzivatel) ? $user->setId($logsData->id_uzivatel) : NULL;
            isset($logsData->jmeno) ? $user->setName($logsData->jmeno) : NULL;
            isset($logsData->uzivatelske_jmeno) ? $this->username = $logsData->uzivatelske_jmeno : NULL;
            isset($logsData->heslo) ? $this->password = $logsData->heslo : NULL;
            $this->user = $user;
            $this->setUser($user);
            $event = new Events($this->database);
            isset($logsData->id_udalost) ? $event->setId($logsData->id_udalost) : NULL;
            isset($logsData->nazev) ? $event->setName($logsData->nazev) : NULL;
            $this->setEvent($event);
            $this->timestamp = isset($logsData->datum) ? $this->timestamp = $logsData->datum : NULL;
        }
    }

    public function insertLog($logData) {
        try {
            $this->setLog($logData);
            $this->createLog();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function setUnsuccessfulLoginLog(Users $user) {
        $this->user = $user;
        if ($user->getId() != null) {
            $event = new Events($this->database);
            $event->setId(Events::EVENT_TYPE['Přihlášení web - neúspěšné - neplatné heslo']);
            $this->event = $event;
            $this->timestamp = date('Y-m-d H:i:s');
        } else {
            $event = new Events($this->database);
            $event->setId(Events::EVENT_TYPE['Přihlášení web - neúspěšně - neplatné jméno i heslo']);
            $this->event = $event;
            $this->timestamp = date('Y-m-d H:i:s');
        }

        try {
            $this->createLog();
        } catch (Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setSuccessfulLoginLog(Users $user) {
        $this->user = $user;
        $event = new Events($this->database);
        $event->setId(Events::EVENT_TYPE['Přihlášení web - úspěšné']);
        $this->event = $event;
        $this->timestamp = date('Y-m-d H:i:s');
        try {
            $this->createLog();
        } catch (Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setSuccessfulLogoutLog(Users $user) {
        $this->user = $user;
        $event = new Events($this->database);
        $event->setId(Events::EVENT_TYPE['Odhlášení web - odhlášení vynucené uživatelem']);
        $this->event = $event;
        $this->timestamp = date('Y-m-d H:i:s');
        try {
            $this->createLog();
        } catch (Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    private function createLog() {
        return $this->database->query('INSERT INTO uzivatele.log_udalosti(id_uzivatel, id_udalost, datum, uzivatelske_jmeno, heslo) VALUES (?,?,?,?,?)', !is_null($this->user->getId()) ? (int) $this->user->getId() : null, $this->event ? (int) $this->event->getId() : null, $this->timestamp, !is_null($this->user->getUsername()) ? $this->user->getUsername() : null, !is_null($this->user->getPassword()) ? $this->user->getPassword() : null)->fetch();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
