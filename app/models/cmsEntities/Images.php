<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CmsEntity;

use App\Model\Entity\CmsEntity\Documents;

/**
 * Description of Images
 *
 * @author inComputer
 */
class Images extends Documents {

    private $titleImage;

    public function getTitleImage() {
        return $this->titleImage;
    }

    protected function setImage($imageData) {
        if (!empty($imageData)) {
            $this->id = isset($imageData->id_obrazek) ? $imageData->id_obrazek : NULL;
            $this->filename = isset($imageData->nazev) ? $imageData->nazev : NULL;
            $this->titleImage = $imageData->cms_titulni_obrazek;
            $this->uploadYear = $imageData->rok_nahrani;
            $this->uploadDate = $imageData->datum_nahrani;
        }
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
