<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of Player
 *
 * @author inComputer
 */
class Players extends BasicEntity {

    private
            $id,
            $name,
            $sex,
            /*  OLD SOLUTION WITH THE BIRTH NUMBER
             * $birthNumber,
             */
            $birthYear,
            $hand,
            $height,
            $weight,
            $slug,
            $descriptions,
            $activeYears;

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function setBirthYear($birthYear) {
        $this->birthYear = $birthYear;
    }

    public function setSex($sex) {
        $this->sex = $sex;
    }
    
    public function setSlug($slug) {
        $this->slug = $slug;
    }
    
    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSex() {
        return $this->sex;
    }

    public function getBirthYear() {
        return $this->birthYear;
    }

    public function getHand() {
        return $this->hand;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getWeight() {
        return $this->weight;
    }
    
    public function getSlug() {
        return $this->slug;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }
    
    public function getActiveYears() {
        return $this->activeYears;
    }

    protected function setPlayer($playerData) {
        if ($playerData) {
            $this->id = isset($playerData->id_hrac) ? $playerData->id_hrac : NULL;
            $this->name = isset($playerData->jmeno) ? $playerData->jmeno : NULL;
            $this->sex = isset($playerData->pohlavi) ? $playerData->pohlavi : NULL;
            $this->birthYear = isset($playerData->rok_narozeni) && $playerData->rok_narozeni != '' ? $playerData->rok_narozeni : NULL;
            $this->hand = isset($playerData->ruka) ? $playerData->ruka : NULL;
            $this->height = isset($playerData->vyska) ? $playerData->vyska : NULL;
            $this->weight = isset($playerData->vaha) ? $playerData->vaha : NULL;
            $this->slug = isset($playerData->hrac_slug) ? $playerData->hrac_slug: NULL;
            $this->descriptions = isset($playerData->hrac_info) && $playerData->hrac_info != '' ? $this->descriptions = $playerData->hrac_info : NULL;
        }
    }

    public function insertPlayer($playerData) {
        try {
            $this->setPlayer($playerData);
            $this->createPlayer();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updatePlayer($id, $playerData) {
        try {
            $this->setPlayer($playerData);
            $this->setId($id);
            $this->editPlayer();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deletePlayer() {
        try {
            $this->erasePlayer();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcPlayer() {
        try {
            if (!empty($this->getId())) {
                $values = $this->readPlayerById();
            }
            else {
                $values = $this->readPlayerBySlug();
            }
            $this->setPlayer($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function calcActiveYears() {
        try {
            $years = $this->readPlayerActiveYears();
            $activeYears = array();
            $activeYears[] = 0;
            foreach ($years as $year) {
                $activeYears[] = $year->roc;
            }
            $this->activeYears = $activeYears;
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    /*
      public function getPlayerById()
      {
      try {
      return $this->readPlayerById();
      } catch (Nette\Neon\Exception $e) {
      return $e;
      }
      }
     * 
     */
    
    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_hrac VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', NULL, $this->name, $this->sex, $this->hand, $this->height, $this->weight, $this->descriptions, $this->birthYear, FALSE, TRUE, FALSE, FALSE)->fetch();
    }
    
    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_hrac VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->sex, $this->hand, $this->height, $this->weight, $this->descriptions, $this->birthYear, FALSE, FALSE, TRUE, FALSE)->fetch();
    }
    
    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_hrac VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->sex, $this->hand, $this->height, $this->weight, $this->descriptions, $this->birthYear, FALSE, FALSE, FALSE, TRUE)->fetch();
    }

    private function createPlayer() {
        return $this->database->query('SELECT * FROM hraci_vkladani(?,?,?,?,?,?,?)', $this->name, $this->sex, $this->hand, $this->birthYear, (int) $this->height, (int) $this->weight, $this->descriptions
                )->fetch();
    }

    private function editPlayer() {
        return $this->database->query('SELECT * FROM hraci_uprava(?,?,?,?,?,?,?,?)', (int) $this->id, $this->name, $this->sex, $this->hand, $this->birthYear, (int) $this->height, (int) $this->weight, $this->descriptions
                )->fetch();
    }

    private function readPlayerActiveYears() {
        return $this->database->query('SELECT DISTINCT roc FROM hraci_zapasy(?)', $this->id)->fetchAll();
    }

    private function readPlayerById() {
        return $this->database->query('SELECT * FROM hrac WHERE id_hrac = ?', $this->id)->fetch();
    }
    
    private function readPlayerBySlug() {
        return $this->database->query('SELECT * FROM hrac WHERE hrac_slug = ?', $this->slug)->fetch();
    }

    private function erasePlayer() {
        return $this->database->query('DELETE FROM hrac WHERE id_hrac = ?', (int) $this->id)->fetch();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
