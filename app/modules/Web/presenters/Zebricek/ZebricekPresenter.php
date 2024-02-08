<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\PlayerStatsList;
use App\Model\Entity\SportEntity\CompetitionsList;
use App\Model\Entity\Messenger\PlayerStatsListMessenger;

class ZebricekPresenter extends BasicPresenter {

    private $playerStatsList,
            $competitionsList,
            $seasonYear,
            $playerSex,
            $matchType;
    private $ajax = false;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, CompetitionsList $competitionsList, PlayerStatsList $playerStatsList) {//Ranking $ranking) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->competitionsList = $competitionsList;
        $this->playerStatsList = $playerStatsList;
    }

    public function beforeRender() {
        parent::beforeRender();

        $this->template->selectedYear = $this->seasonYear = isset($this->seasonYear) ? $this->seasonYear : date('Y');
        $this->playerSex = $this->playerSex ?? 'Všechna pohlaví';
        $this->matchType = $this->matchType ?? 'Všechny typy zápasů';
    }

    public function renderDefault() {
        if (!$this->ajax) {
            parent::renderDefault();
            $this->competitionsList->calcActiveYears();
            $this->template->competitions = $this->competitionsList;
            $this->template->matchTypeList = $this->competitionsList::MATCH_TYPE;
            $this->template->playerSexList = $this->competitionsList::PLAYER_SEX;
            if (!in_array($this->seasonYear, $this->competitionsList->getActiveYears())) {
                $this->template->currentEmptyYear = $this->seasonYear;
            }
        }
        $this->getRanking();
    }

    private function getRanking() {
        $this->playerStatsList->setMatchTypeName($this->matchType);
        $this->playerStatsList->setPlayerSexKey($this->playerSex);
        $this->playerStatsList->setSeasonYear($this->seasonYear);
        $this->playerStatsList->calcPlayersStatsList();
        $playerStatsList = new PlayerStatsListMessenger($this->playerStatsList);
        $this->template->ranking = $playerStatsList->playerStatsListMessenger;
    }

    public function handlePlayerStats($matchType, $playerSex, $seasonYear) {
        $this->matchType = $matchType;
        $this->seasonYear = $seasonYear;
        $this->playerSex = $playerSex;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxPlayerStats');
        }
    }

}
