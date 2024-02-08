<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity;

use Nette;

/**
 * Description of SportEntity
 *
 * @author inComputer
 */
abstract class BasicEntity {

    const MATCH_TYPE = array(
        "Všechny typy zápasů" => 0,
        "Dvouhra muži" => 1,
        "Čtyřhra muži" => 2,
        "Dvouhra ženy" => 3,
        "Čtyřhra mix" => 4);
        //"Čtyřhra ženy" => 5);
    
    const PLAYER_SEX = array(
        "Všechna pohlaví" => 'X',
        "Muži" => 'M',
        "Ženy" => 'Z');
    
    const HAND = array(
        "Pravá" => 'P',
        "Levá" => 'L',);

    /**
     * @var Nette\Database\Context
     */
    protected $database;
    protected $matchTypeId,
            $playerSexValue,
            $seasonYear;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }
    
    public function setMatchTypeId($matchTypeId) {
        try {
            if (!in_array($matchTypeId, BasicEntity::MATCH_TYPE)) {
                throw new \Exception("Chyba nastavení třídy " . get_class() . " - chybně nastavené ID typu zápasu");
            }
            $this->matchTypeId = $matchTypeId;
        } catch (\Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setMatchTypeName($matchType) {
        try {
            if (!array_key_exists($matchType, BasicEntity::MATCH_TYPE)) {
                throw new \Exception("Chyba nastavení třídy " . get_class() . " - chybně nastavený typ zápasu");
            }
            $this->matchTypeId = BasicEntity::MATCH_TYPE[$matchType];
        } catch (\Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setPlayerSexKey($playerSexKey) {
        try {
            if (!array_key_exists($playerSexKey, BasicEntity::PLAYER_SEX)) {
                throw new \Exception("Chyba nastavení třídy " . get_class() . " - chybně nastavené pohlaví hráče");
            }
            $this->playerSexValue = BasicEntity::PLAYER_SEX[$playerSexKey];
        } catch (\Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setSeasonYear($seasonYear) {
        $this->seasonYear = $seasonYear;
    }
    
    public function getMatchTypeId()
    {
        return $this->matchTypeId;
    }
    
    public function getMatchTypeName()
    {
        return array_search($this->matchTypeId, $this::MATCH_TYPE);
    }
    
    public function getPlayerSexValue()
    {
        return $this->playerSexValue;
    }
    
    public function getPlayerSexKey()
    {
        return array_search($this->playerSexValue, $this::PLAYER_SEX);
    }
    
    public function getSeasonYear()
    {
        return $this->seasonYear;
    }

    abstract protected function getNewInstance();
}
