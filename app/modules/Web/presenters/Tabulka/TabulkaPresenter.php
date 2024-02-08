<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\CompetitionsList;

class TabulkaPresenter extends BasicPresenter {

    private
            $seasonYear, // proměnná pro příjem zvoleného roku v handleClubTable
            $competition, // proměnná pro příjem zvolené soutěže v handleClubTable
            $matchType,
            $ajax = false,
            $competitionsList;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, CompetitionsList $competitionsList) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->competitionsList = $competitionsList;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->selectedYear = $this->seasonYear = isset($this->seasonYear) ? $this->seasonYear : date('Y');
        //bdump($this->seasonYear); // show current season year in the debug panel
        $this->template->selectedCompetition = $this->competition = $this->competition ?? 0;
        $this->template->matchType = $this->matchType = $this->matchType ?? "Všechny typy zápasů";
    }

    public function renderDefault() {
        if (!$this->ajax) {
            parent::renderDefault();
            $this->competitionsList->calcActiveYears();
            $this->competitionsList->calcCompetitionsList();
            $this->template->matchTypeList = $this->competitionsList::MATCH_TYPE;
            if (!in_array($this->seasonYear,$this->competitionsList->getActiveYears())){
                $this->template->currentEmptyYear = $this->seasonYear;
            }
            $this->template->competitionsList = $this->competitionsList;
        }
        $this->getTable();
    }

    private function getTable() {
        $this->clubStatsList = $this->clubStatsList->getNewInstance();
        $this->clubStatsList->setCompetition($this->competition);
        $this->clubStatsList->setMatchTypeName($this->matchType);
        $this->clubStatsList->setSeasonYear($this->seasonYear);
        $this->clubStatsList->calcClubStatsList();
        $this->template->fullTable = $this->clubStatsList->getClubStatsList();
    }

    public function handleClubStats($matchType, $competition, $seasonYear) {
        $this->matchType = $matchType;
        $this->seasonYear = $seasonYear;
        $this->competition = $competition;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxClubStats');
        }
    }

}
