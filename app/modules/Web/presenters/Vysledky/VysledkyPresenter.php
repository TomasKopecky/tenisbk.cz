<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\MatchesTableList;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\CompetitionsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\RoundsList;

class VysledkyPresenter extends BasicPresenter {

    private $matchesTableList,
            $roundsList,
            $competitionsList,
            $ajax = false,
            $seasonYear,
            $matchType,
            $round,
            $competition;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, CompetitionsList $competitionsList, MatchesTableList $matchesTableList, RoundsList $roundsList) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->matchesTableList = $matchesTableList;
        $this->competitionsList = $competitionsList;
        $this->roundsList = $roundsList;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->selectedYear = $this->seasonYear = isset($this->seasonYear) ? $this->seasonYear : date('Y');
        $this->template->selectedCompetition = $this->competition = $this->competition ?? 0;
        $this->template->selectedRound = $this->round = $this->round ?? 0;
        $this->matchType = $this->matchType ?? "Všechny typy zápasů";
    }

    public function renderDefault() {
        if (!$this->ajax) {
            parent::renderDefault();
            $this->competitionsList->calcActiveYears();
            $this->competitionsList->calcCompetitionsList();
            $this->roundsList->setSeasonYear($this->seasonYear);
            $this->roundsList->CalcRoundsList();
            $this->template->matchTypeList = $this->competitionsList::MATCH_TYPE;
            $this->template->roundsList = $this->roundsList->getRoundsList();
            $this->template->competitionsList = $this->competitionsList;
            !isset($this->template->playsTable) ? $this->getPlays() : NULL;
            if (!in_array($this->seasonYear,$this->competitionsList->getActiveYears())){
                $this->template->currentEmptyYear = $this->seasonYear;
            }
            $this->getMatches();
        } else {
            $this->competitionsList->setId($this->competition);
            $this->roundsList->setNumber($this->round);
            !isset($this->template->playsTable) ? $this->getPlays() : NULL;
            $this->getMatches();
        }
    }

    private function getPlays() {
        $this->playsTableList->setSeasonYear($this->seasonYear);
        $this->playsTableList->setCompetition($this->competitionsList);
        $this->playsTableList->setRound($this->roundsList);
        $this->playsTableList->calcPlaysTableList();
        $this->template->playsTable = $this->playsTableList->getPlaysTableList();
    }

    private function getMatches() {
        $this->matchesTableList->setSeasonYear($this->seasonYear);
        $this->matchesTableList->setCompetition($this->competitionsList);
        $this->matchesTableList->setMatchTypeName($this->matchType);
        $this->matchesTableList->setRound($this->roundsList);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
    }

    public function handleResults($competition, $seasonYear, $round, $matchType) {

        $this->seasonYear = $seasonYear;
        $this->competition = $competition;
        $this->round = $round;
        $this->matchType = $matchType;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxResults');
        }
    }

}
