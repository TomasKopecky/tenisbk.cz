<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\BasicTools;

use App\Model\Entity\SportEntity\Players;
/**
 * Description of CheckTools
 *
 * @author TOM
 */
class CheckTools {
    
    private $players;
    
    public function __construct(Players $players) {
        $this->players = $players;
    }
    
    public function idPlayerCheck($id, $presenter, $redirect)
    {
        // při zadání nečíselného parametru ID hráče
        if (!is_numeric($id)) {
            $presenter->flashMessage('Špatně zadané ID hráče', 'error');
            $presenter->redirect($redirect);
        }       
        
        $this->players->setId($id);
        $this->players->calcPlayer(); // při zadání ID hráče, kterého databáze neobsahuje        
        if($this->players->getId() == NULL) {
             $presenter->flashMessage('Hráč daného ID se nenachází v databázi', 'error');
             $presenter->redirect($redirect);            
        }
        
        return $this->players;
    } 
        
    public function playerMatchesCheck($id, $matchType, $year, $redirect)
    {
        $result = $this->postgre_tennis_db->getPlayerStat($id,$matchType, $year);
        
        // při zadání ID hráče, který neodehrál žádný zápas
        if ($result==null) {
            $this->flashMessage('Hráč daného ID neodehrál dosud žádný zápas','error');
            $this->redirect($redirect);            
        }
        
        return $result;
    }
    //put your code here
}
