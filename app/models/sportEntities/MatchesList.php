<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Matches;
/**
 * Description of MatchList
 *
 * @author inComputer
 */
class MatchesList extends Matches {
    
    public $matchesList = array();
    
    public function getMatches()
    {
        
    }
    
    private function setMatches($matchesData)
    {
        foreach ($matchesData as $match)
        {
            $newMatch = new Matches;
            $newMatch->player = $match->            
            $this->matchesList[] = (new Matches());
        }
    }
    
    private function readMatchesByPlayerId()
    {
        return $this->database->query('SELECT * FROM hraci_zapasy(?,?,?)', $this->player->getId(),$this->matchTypeId,$this->seasonYear)->fetchAll();
    }
    
    public function getNewInstance()
    {
        return new self($this->database);
    }
}
