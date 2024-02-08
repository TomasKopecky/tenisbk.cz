<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\UserEntity;

use App\Model\Entity\BasicEntity;
/**
 * Description of Roles
 *
 * @author inComputer
 */
class Events extends BasicEntity{
    
    const EVENT_TYPE = array(
        "Přihlášení web - úspěšné" => 1,
        "Přihlášení web - neúspěšné - neplatné heslo" => 2,
        "Přihlášení web - neúspěšně - neplatné jméno i heslo" => 3,
        "Odhlášení web - odhlášení vynucené uživatelem" => 4
        );
    
    private $id,
            $name,
            $descriptions;
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function setDescriptions($descriptions)
    {
        $this->descriptions = $descriptions;
    }
    
        public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDescriptions()
    {
        return $this->descriptions;
    }
    
    public function getNewInstance()
    {
        return new self($this->database);
    }
}
