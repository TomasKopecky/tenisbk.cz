<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\Clubs;
use App\Model\Entity\SportEntity\ClubStats;
use App\Model\Entity\SportEntity\CompetitionsList;

class KlubyPresenter extends BasicPresenter {

    private
            $seasonYear,
            $competition,
            $tab, // proměnná pro příjem aktivního tabu v handlePlayerStatYear
            $clubs,
            $clubStats,
            $ajax = false,
            $competitionsList;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, Clubs $clubs, ClubStats $clubStats, CompetitionsList $competitionsList) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->clubs = $clubs;
        $this->clubStats = $clubStats;
        $this->competitionsList = $competitionsList;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->selectedYear = $this->seasonYear = isset($this->seasonYear) ? $this->seasonYear : date('Y');
        $this->template->selectedCompetition = $this->competition = isset($this->competition) ? $this->competition : 0;
        $this->template->selectedTab = $this->tab = isset($this->tab) ? $this->tab : 'Dvouhra M';
    }

    public function renderDefault() {
        $this->redirect(':Web:Tabulka:'); // protože není sekce /hraci bez id užívána, při přístupu do ní, přesměrujemem do sekce /zebricek, odkud se nejčastěji přistupuje do detailů o hráčích
    }

    public function renderDetail($slug) {
        //$this->clubs->setId($id);
        $this->clubs->setSlug($slug);
        $this->clubs->calcClub();
        $this->clubs->calcActiveYears();
        !in_array($this->seasonYear, $this->clubs->getActiveYears()) ? $this->template->selectedYear = $this->seasonYear = 0 : NULL;
        if (!$this->ajax) {
            parent::renderDefault();
        /*  old solution with the getting a club from a database by id
            if (is_null($this->clubs->getName())) {
                $this->flashMessage('Klub daného ID v databázi neexistuje', 'error');
                $this->redirect(':Web:Tabulka:');
            }
         * 
         */
            if (is_null($this->clubs->getName())) {
                $this->flashMessage('Špatně zadaná URL pro přístup na stránku klubu', 'error');
                $this->redirect('default');
            }
            $this->getClubCompleteStats();
            $this->competitionsList->setClub($this->clubs);
            $this->competitionsList->getClubCompetitions();
            $this->template->competitionsList = $this->competitionsList;
            $this->getClubInfo();
            $this->GetClubStats();
            $this->getClubPlays();
        } else {
            $this->competitionsList->getNewInstance();
            $this->competitionsList->setId($this->competition);
            $this->getClubCompleteStats();
            $this->getClubInfo();
            $this->getClubStats();
            $this->getClubPlays();
        }
    }

    private function getClubCompleteStats() {
        $this->clubStats->setClubs($this->clubs);
        $this->clubStats->setSeasonYear($this->seasonYear);
        $this->clubStats->setMatchTypeName("Všechny typy zápasů");
        $this->clubStats->setCompetition($this->competitionsList);
        $this->clubStats->getClubStats();
        $this->template->clubStats = $this->clubStats;
    }

    private function getClubPlays() {
        $playsTableList = $this->playsTableList->getNewInstance();
        $playsTableList->setClub($this->clubs);
        $playsTableList->setCompetition($this->competitionsList);
        $playsTableList->setSeasonYear($this->seasonYear);
        $playsTableList->calcPlaysTableList();
        $this->template->playsTableList = $playsTableList->getPlaysTableList();
    }

    private function GetClubStats() { // počáteční inicializace statistiky hráčů - předání proměnných z databázových funkcí z modelu do šablony
        $menSinglesStats = $this->clubStats->getNewInstance();
        $menSinglesStats->setClubs($this->clubs);
        $menSinglesStats->setSeasonYear($this->seasonYear);
        $menSinglesStats->setMatchTypeName("Dvouhra muži");
        $menSinglesStats->setCompetition($this->competitionsList);
        $menSinglesStats->getClubStats();
        $this->template->menSinglesStats = $menSinglesStats;

        $womenSinglesStats = $this->clubStats->getNewInstance();
        $womenSinglesStats->setClubs($this->clubs);
        $womenSinglesStats->setSeasonYear($this->seasonYear);
        $womenSinglesStats->setMatchTypeName("Dvouhra ženy");
        $womenSinglesStats->setCompetition($this->competitionsList);
        $womenSinglesStats->getClubStats();
        $this->template->womenSinglesStats = $womenSinglesStats;

        $menDoublesStats = $this->clubStats->getNewInstance();
        $menDoublesStats->setClubs($this->clubs);
        $menDoublesStats->setSeasonYear($this->seasonYear);
        $menDoublesStats->setMatchTypeName("Čtyřhra muži");
        $menDoublesStats->setCompetition($this->competitionsList);
        $menDoublesStats->getClubStats();
        $this->template->menDoublesStats = $menDoublesStats;

        $doublesMixStats = $this->clubStats->getNewInstance();
        $doublesMixStats->setClubs($this->clubs);
        $doublesMixStats->setSeasonYear($this->seasonYear);
        $doublesMixStats->setMatchTypeName("Čtyřhra mix");
        $doublesMixStats->setCompetition($this->competitionsList);
        $doublesMixStats->getClubStats();
        $this->template->doublesMixStats = $doublesMixStats;
    }

    private function getClubInfo() {
        $this->template->club = $this->clubs; // informace o klubu daného id z databáze
        //$this->clubs->setPlayer($this->players);
        //$this->clubs->getClubsByPlayer();
        //$this->template->klub = $this->clubs;
    }

    public function handleClubStatYear($seasonYear, $selectedCompetition, $activeTab) { // handle na ajax událost (změna roku) v šabloně s detaily o hráči
        $this->seasonYear = $seasonYear;
        $this->competition = $selectedCompetition;
        $this->tab = $activeTab;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxClubStat');
        }
    }

}
