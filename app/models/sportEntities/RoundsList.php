<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Rounds;

/**
 * Description of ClubStats
 *
 * @author TOM
 */
class RoundsList extends Rounds{
    
    private $roundsList = array();

     private function setRoundsList($roundsData)
    {
         foreach ($roundsData as $rounds)
         {
             $round = new Rounds($this->database);
             $round->setRounds($rounds);
             $this->roundsList[] = $round;
         }
    }
    
    public function CalcRoundsList() {
        try {
            $values = $this->readRoundsList();
            $this->setRoundsList($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    private function readRoundsList()
    {
        if ($this->getNumber() == 0)
        {
        return $this->database->query('SELECT DISTINCT kolo FROM ucast_na_utkani ORDER BY kolo ASC')->fetchAll();
        }
        else
        {
           return $this->database->query('SELECT DISTINCT kolo FROM ucast_na_utkani WHERE rocnik = ? ORDER BY kolo ASC', (int) $this->getSeasonYear())->fetchAll();
        
        }
        
        }
        
        public function getRoundsList()
        {
            return $this->roundsList;
        }
    
    public function getNewInstance()
    {
        return new self($this->database);
    }
}
