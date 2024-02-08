<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\Players;
use App\Model\Entity\SportEntity\ClubsList;
use App\Model\Entity\SportEntity\PlayerStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\MatchesTableList;

class HraciPresenter extends BasicPresenter {

    private
            $seasonYear, // proměnná pro příjem zvoleného roku v handlePlayerStatYear
            $tab, // proměnná pro příjem aktivního tabu v handlePlayerStatYear
            $players,
            $clubs,
            $playerStatsList,
            $matchesTableList,
            $ajax = false;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, Players $players, ClubsList $clubs, PlayerStatsList $playerStatsList, MatchesTableList $matchesTableList) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->players = $players;
        $this->clubs = $clubs;
        $this->playerStatsList = $playerStatsList;
        $this->matchesTableList = $matchesTableList;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->selectedYear = $this->seasonYear = isset($this->seasonYear) ? $this->seasonYear : date('Y');
        $this->template->selectedTab = $this->tab = $this->tab ?? 'Dvouhra';
    }

    public function renderDefault() {
        $this->redirect(':Web:Zebricek:'); // protože není sekce /hraci bez id užívána, při přístupu do ní, přesměrujemem do sekce /zebricek, odkud se nejčastěji přistupuje do detailů o hráčích
    }

    public function renderDetail($slug) {
        //$this->players->setId($id);
        $this->players->setSlug($slug);
        $this->players->calcPlayer();
        $this->players->calcActiveYears();
        !in_array($this->seasonYear, $this->players->getActiveYears()) ? $this->template->selectedYear = $this->seasonYear = 0 : NULL;
        if (!$this->ajax) {
            parent::renderDefault();
            /* old solution with the id and slug combination of getting from a database
              if (is_null($this->players->getName())) {
              $this->flashMessage('Hráč daného ID v databázi neexistuje', 'error');
              $this->redirect(':Web:Zebricek:');
              }

              if ($this->players->getSlug() != $slug) {
              $this->flashMessage('Špatně zadaná URL pro přístup na stránku hráče', 'error');
              $this->redirect('default');
              }
             * 
             */
            if (is_null($this->players->getName())) {
                $this->flashMessage('Špatně zadaná URL pro přístup na stránku hráče', 'error');
                $this->redirect('default');
            }
            $this->getPlayerInfo();
            $this->getPlayerStats();
            $this->getPlayerMatches();
        } else {
            $this->getPlayerInfo();
            $this->getPlayerStats();
            $this->getPlayerMatches();
        }
    }

    private function getPlayerStats() {
        $this->playerStatsList->setPlayer($this->players);
        $this->playerStatsList->setSeasonYear($this->seasonYear);
        $this->playerStatsList->setPlayerSexKey("Všechna pohlaví");
        $this->playerStatsList->setMatchTypeName("Všechny typy zápasů");
        $this->playerStatsList->calcPlayerStatsParticular();
        $this->playerStatsCalc();
        //$this->playerStatsList->getPlayerStatsList();
        //$this->template->playerStatsAll = $this->playerStatsList->getPlayerStatsList();
    }

    private function playerStatsSet($statsType, $stats) {
        $statsType->setMatches($statsType->getMatches() + $stats->getMatches());
        $statsType->setWins($statsType->getWins() + $stats->getWins());
        $statsType->setDefeats($statsType->getDefeats() + $stats->getDefeats());
        $statsType->setWonSets($statsType->getWonSets() + $stats->getWonSets());
        $statsType->setLostSets($statsType->getLostSets() + $stats->getLostSets());
        $statsType->setWonGames($statsType->getWonGames() + $stats->getWonGames());
        $statsType->setLostGames($statsType->getLostGames() + $stats->getLostGames());
        $statsType->setSuccessRate($statsType->getSuccessRate() + $stats->getSuccessRate());
    }

    private function playerStatsCalc() {
        $singlesIndex = $doublesIndex = $mixIndex = 0;
        $allStats = $this->playerStatsList->getPlayerStatsList();
        $singles = $this->playerStatsList->getNewInstance();
        $doubles = $this->playerStatsList->getNewInstance();
        $doublesMix = $this->playerStatsList->getNewInstance();
        $playerStatsAll = $this->playerStatsList->getNewInstance();
        $playerStatsAll->setRankingPosition($this->playerStatsList->getRankingPosition());
        foreach ($allStats as $stats) {
            if ($stats->getMatchTypeName() == 'Dvouhra muži' || $stats->getMatchTypeName() == 'Dvouhra ženy') {
                $this->playerStatsSet($singles, $stats);
                $singlesIndex++;
            }
            if ($stats->getMatchTypeName() == 'Čtyřhra muži' /* || $stats->getMatchTypeName() == 'Čtyřhra ženy'*/) {
                $this->playerStatsSet($doubles, $stats);
                $doublesIndex++;
            }
            if ($stats->getMatchTypeName() == 'Čtyřhra mix') {
                $this->playerStatsSet($doublesMix, $stats);
                $mixIndex++;
            }
            $this->playerStatsSet($playerStatsAll, $stats);
        }
        //print_r($playerStatsAll);
        $singlesIndex > 0 ? $singles->setSuccessRate($singles->getSuccessRate()/$singlesIndex) : NULL;
        $doublesIndex > 0 ? $doubles->setSuccessRate($doubles->getSuccessRate()/$doublesIndex) : NULL;
        $mixIndex > 0 ? $doublesMix->setSuccessRate($doublesMix->getSuccessRate()/$mixIndex) : NULL;
        
        $this->template->singlesStats = $singles;
        $this->template->doublesStats = $doubles;
        $this->template->doublesMixStats = $doublesMix;
        $this->template->playerStatsAll = $playerStatsAll;
    }

    private function getPlayerMatches() {
        $this->matchesTableList->setPlayer($this->players);
        $this->matchesTableList->setSeasonYear($this->seasonYear);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matches = $this->matchesTableList->getMatchesTableList();
    }

    private function getPlayerInfo() {
        $this->template->player = $this->players;
        $this->clubs->setPlayer($this->players);
        $this->clubs->calcClubsByPlayer();
        //bdump($this->clubs);
        $this->template->clubs = $this->clubs->getClubsList();
    }

    public function handlePlayerStatYear($year, $tab) { // handle na ajax událost (změna roku) v šabloně s detaily o hráči
        $this->seasonYear = $year;
        $this->tab = $tab;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxPlayerStats');
        }
    }

}
