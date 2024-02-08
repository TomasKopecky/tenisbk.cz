<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\Messenger;

use App\Model\Entity\SportEntity\PlayerStatsList;
use App\Model\Entity\Messenger\PlayerStatsMessenger;
/**
 * Description of PlayerStatsListMessenger
 *
 * @author TOM
 */
class PlayerStatsListMessenger {
    public $playerStatsListMessenger = array();
    
    public function __construct(PlayerStatsList $playerStatsList) {
        $this->calcPlayerStatsList($playerStatsList->getPlayerStatsList());
    }
    
    private function calcPlayerStatsList($playerStatsList)
    {
        foreach ($playerStatsList as $stats)
        {
            $playerStatsMessenger = new PlayerStatsMessenger($stats);
            $this->playerStatsListMessenger[] = $playerStatsMessenger;
        }
    }
}
