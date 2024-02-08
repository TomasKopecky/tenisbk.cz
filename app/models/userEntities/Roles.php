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
class Roles extends BasicEntity{
    
    private $id,
            $title,
            $descriptions;
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function setDescriptions($descriptions)
    {
        $this->descriptions = $descriptions;
    }
    
    protected function setRole($roleData) {
    if ($roleData){

        $this->id = isset($roleData->id_role) ? $roleData->id_role : NULL;
        $this->title = isset($roleData->nazev) ? $roleData->nazev : NULL;
        $this->descriptions = isset($roleData->role_info) ?? $roleData->role_info != '' ? $roleData->role_info : NULL;
    }
    }
    
        public function calcRole() {
        try {
            $values = $this->readRole();
            $this->setRole($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
        private function readRole() {
        return $this->database->query('SELECT * FROM uzivatele.role WHERE id_role = ?', (int) $this->id)->fetch();
    }
    
        public function getId()
    {
        return $this->id;
    }
    
    public function getTitle()
    {
        return $this->title;
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
