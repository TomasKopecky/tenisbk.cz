<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity;

use App\Model\Entity\BasicEntity;

/**
 * Description of SportEntity
 *
 * @author inComputer
 */
abstract class FuncEntity extends BasicEntity {

    protected 
            $matchType,
            $playerSex,
            $seasonYear;

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

    public function setPlayerSexKey($playerSex) {
        try {
            if (!array_key_exists($playerSex, BasicEntity::PLAYER_SEX)) {
                throw new \Exception("Chyba nastavení třídy " . get_class() . " - chybně nastavené pohlaví hráče");
            }
            $this->playerSexId = BasicEntity::PLAYER_SEX[$playerSex];
        } catch (\Nette\Neon\Exception $ex) {
            return $ex;
        }
    }

    public function setSeasonYear($seasonYear) {
        $this->seasonYear = $seasonYear;
    }

    protected abstract function checkSetters();

}
