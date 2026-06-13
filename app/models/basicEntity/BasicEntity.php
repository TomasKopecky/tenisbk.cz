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
        "Všechny typy zápasů",
        "Dvouhra muži",
        "Čtyřhra muži",
        "Dvouhra ženy",
        "Čtyřhra mix"
    );
    
    const PLAYER_SEX = array(
        "Všechna pohlaví" => 'X',
        "Muži" => 'M',
        "Ženy" => 'Z');
    
    const HAND = array(
        "Pravá" => 'P',
        "Levá" => 'L',);

    const MATCH_STANDARD_COUNT = array(
        self::MATCH_TYPE[1] => 3,
        self::MATCH_TYPE[2] => 1,
        self::MATCH_TYPE[3] => 1,
        self::MATCH_TYPE[4] => 1
    );

    const MATCH_ALTERNATIVE_COUNT = array(
        self::MATCH_TYPE[1] => 4,
        self::MATCH_TYPE[2] => 2,
        self::MATCH_TYPE[3] => 0,
        self::MATCH_TYPE[4] => 0
    );

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
            if (!array_key_exists($matchTypeId, BasicEntity::MATCH_TYPE)) {
                throw new \Exception("Chyba nastavení třídy " . get_class() . " - chybně nastavené ID typu zápasu");
            }
            $this->matchTypeId = $matchTypeId;
        } catch (\Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setMatchTypeName($matchTypeName) {
        try {
            if (!in_array($matchTypeName, BasicEntity::MATCH_TYPE)) {
                throw new \Exception("Chyba nastavení třídy " . get_class() . " - chybně nastavený typ zápasu");
            }
            $this->matchTypeId = array_search($matchTypeName, BasicEntity::MATCH_TYPE);
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
        return $this::MATCH_TYPE[$this->matchTypeId];
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
