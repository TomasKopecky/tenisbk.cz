<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CmsEntity;

use App\Model\Entity\CmsEntity\Images;

/**
 * Description of ImagesList
 *
 * @author inComputer
 */
class ImagesList extends Images {

    private $imagesList = array();

    private function setImagesList($imagesData) {
        foreach ($imagesData as $image) {
            $newImage = new Images($this->database);
            $newImage->setImage($image);
            $this->imagesList[] = $newImage;
        }
    }

    public function calcImagesList($onlyCmsTitleImages) {
        try {
            if ($onlyCmsTitleImages) {
                $values = $this->readCmsTitleImagesList();
            } else {
                $values = $this->readImagesList();
            }
            $this->setImagesList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getImagesList() {
        return $this->imagesList;
    }

    private function readImagesList() {
        return $this->database->query('SELECT * FROM cms.obrazek WHERE cms_titulni_obrazek = FALSE ORDER BY nazev')->fetchAll();
    }

    private function readCmsTitleImagesList() {
        return $this->database->query('SELECT * FROM cms.obrazek WHERE cms_titulni_obrazek = TRUE ORDER BY nazev')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
