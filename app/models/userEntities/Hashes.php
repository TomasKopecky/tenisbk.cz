<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\UserEntity;
use App\Model\Entity\BasicEntity;

/**
 * Description of Hashes
 *
 * @author inComputer
 */
class Hashes extends BasicEntity{
    
    private $hashCode;
    
    public function setHashCode($hash)
    {
        $this->hashCode = $hash;
    }
    
    public function getHashCode()
    {
        return $this->hashCode;
    }
    
    
    
    public function getNewInstance()
    {
        return new self($this->database);
    }

    //put your code here
}
