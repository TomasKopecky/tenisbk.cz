<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CmsEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of Matches
 *
 * @author inComputer
 */
abstract class Documents extends BasicEntity {

    protected 
            $id,
            $filename,
            $uploadYear,
            $uploadDate,
            $hash;

    public function setId($id) {
        $this->id = $id;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }
    
    public function setUploadDate($uploadDate){
        $this->uploadDate = $uploadDate;
    }
    
    public function setUploadYear($uploadYear) {
        $this->uploadYear = $uploadYear;
    }
    
    public function setHash($hash) {
        $this->hash = $hash;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getFilename()
    {
        return $this->filename;
    }
    
    public function getUploadYear() {
        return $this->uploadYear;
    }
    
    public function getHash() {
        return $this->hash;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
