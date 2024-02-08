<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\Messenger;

use App\Model\Entity\SportEntity\PlayerStats;

class PlayerStatsMessenger {
    
    public  $matches,
            $wins,
            $defeats,
            $wonSets,
            $lostSets,
            $wonGames,
            $lostGames,
            $successRate,
            $player,
            $rankingPosition;
    
    public function __construct(PlayerStats $playerStats) {
        $this->matches = $playerStats->getMatches();
        $this->wins = $playerStats->getWins();
        $this->defeats = $playerStats->getDefeats();
        $this->wonSets = $playerStats->getWonSets();
        $this->lostSets = $playerStats->getLostSets();
        $this->wonGames = $playerStats->getWonGames();
        $this->lostGames = $playerStats->getLostGames();
        $this->successRate = $playerStats->getSuccessRate();
        $this->player = new PlayersMessenger($playerStats->getPlayer());
        $this->rankingPosition = $playerStats->getRankingPosition();
        $this->truePoints = $playerStats->getTruePoints();
    }
}