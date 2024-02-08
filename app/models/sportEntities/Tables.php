<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;
use Nette;
/**
 * Description of Table
 *
 * @author inComputer
 */
class Tables extends BasicEntity {
    
    private $competitions, 
            $year, 
            $date;
    
    public function __construct(Nette\Database\Context $database) {
        parent::__construct($database);
    }
    
    public function setMinitable($competitions, $year, $date)
    {
        $this->competitions = $competitions;
        $this->year = $year;
        $this->date = $date;
    }
    
    public function getCompetitionsMinitable()
    {
        if (sizeof($this->competitions) > 0) {
            
            foreach ($this->competitions as $competition) {
                $minitables[] = $this->getMinitableDatabase($competition);
            }
            return $minitables;
        }
        return null;
        
    }
    
    private function getMinitableDatabase($competitions)
    {
        return $this->database->query('SELECT * FROM tymy_minitabulka(?,?)', $this->year, $competitions->id_soutez)->fetchAll();
    }
    
    public function getPreviousRound()
    {
        return $this->database->query('SELECT MAX (DISTINCT kolo) FROM utkani NATURAL JOIN ucast_na_utkani WHERE rocnik = ? AND utkani_datum < ?', $this->year, $this->date)->fetchField();
    }
    
    public function getNextRound()
    {
        return $this->database->query('SELECT MIN (DISTINCT kolo) FROM utkani NATURAL JOIN ucast_na_utkani WHERE rocnik = ? AND utkani_datum > ?', $this->year, $this->date)->fetchField();
    }
    
    public function getPrevMatchesMinitable()
    {
        return $this->database->query('SELECT * FROM tymy_utkani(?,?,?)', $this->year, $this->getPreviousRound(), 0)->fetchAll();
    }
    
    public function getNextMatchesMinitable()
    {
        return $this->database->query('SELECT * FROM tymy_utkani(?,?,?)', $this->year, $this->getNextRound(), 0)->fetchAll();
    }
    
    public function getNewInstance()
    {
        return new self($this->database);
    }
}
