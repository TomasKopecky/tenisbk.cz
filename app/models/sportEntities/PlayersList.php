<?php

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Players;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class PlayersList extends Players {

    private $playersList = array();

    private function setPlayersList($playersData) {
        foreach ($playersData as $player) {
            $newPlayer = new Players($this->database);
            $newPlayer->setPlayer($player);
            $this->playersList[] = $newPlayer;
        }
    }

    public function calcPlayersList() {
        try {
            $values = $this->readPlayersList();
            $this->setPlayersList($values);
        } catch (Exception $e) {
            return $e;
        }
    }
    
     public function calcPlayersListByYearAndClub($clubId, $sex, $season) {
        try {
            $values = $this->readPlayersListByYearAndClub($clubId, $sex, $season);
            $this->setPlayersList($values);
        } catch (Exception $e) {
            return $e;
        }
    }
    
     private function readPlayersListByYearAndClub($clubId, $sex, $season) {
         if ($sex == 'X')
         {
             return $this->database->query("SELECT DISTINCT id_hrac, jmeno FROM registrace NATURAL JOIN hrac WHERE id_klub = ? AND CAST('1.1.'||? AS DATE) BETWEEN datum_od AND CASE WHEN datum_do IS NULL THEN NOW() ELSE datum_do END", (int) $clubId, (int) $season)->fetchAll();
        
         }
         else
         {
        return $this->database->query("SELECT DISTINCT id_hrac, jmeno FROM registrace NATURAL JOIN hrac WHERE pohlavi = ? AND id_klub = ? AND CAST('1.1.'||? AS DATE) BETWEEN datum_od AND CASE WHEN datum_do IS NULL THEN NOW() ELSE datum_do END", $sex, (int) $clubId, (int) $season)->fetchAll();
         }
    }

    public function getPlayersList() {
        return $this->playersList;
    }

    private function readPlayersList() {
        return $this->database->query('SELECT * FROM hrac ORDER BY jmeno')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

