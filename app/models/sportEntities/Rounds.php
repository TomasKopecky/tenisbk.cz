<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of Results
 *
 * @author inComputer
 */
class Rounds extends BasicEntity {

    private $number;

    public function setNumber($number) {
        $this->number = $number;
    }

    public function setRounds($roundsData) {
        if (!is_null($roundsData)) {
            $this->setNumber($roundsData->kolo);
        }
    }

    public function getNumber() {
        return $this->number;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
