<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\Players;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\MatchesTableList;

class UtkaniPresenter extends BasicPresenter
{
    private $matchesTableList,
            $players;
    
    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, MatchesTableList $matchesTableList, Players $players){
        parent::__construct($clubStatsList, $playsTableList);
        $this->matchesTableList = $matchesTableList;
        $this->players = $players;
    }
    
    public function renderDefault() 
    {
        $this->redirect(':Web:Vysledky:'); // protože není sekce utkání bez id užívána, při přístupu do ní, přesměrujemem do sekce aktuality na homepage
    }
    
    public function renderDetail($id)
    {
             parent::renderDefault();   
        $this->playsTableList->setId($id);
        $this->playsTableList->calcPlaysTable();

            if (is_null($this->playsTableList->getClubHome())) {
                $this->flashMessage('Utkání daného ID v databázi neexistuje', 'error');
                $this->redirect(':Web:Vysledky:');
            }
            $this->getPlayStats();
            $this->getPlaysMatches();
    }
    
    public function getPlayStats()
    {
        $this->template->playsStats = $this->playsTableList;
    }
    
    private function calcPlayStats()
    {
        $setsHome = 0;
        $setsVisitors = 0;
        $gamesHome = 0;
        $gamesVisitors = 0;
        foreach ($this->matchesTableList->getMatchesTableList() as $match)
        {
            $setsHome += $match->getResults()->getSetsHome();
            $setsVisitors += $match->getResults()->getSetsVisitors();
            $gamesHome += $match->getResults()->getSetsHomeTotal();
            $gamesVisitors += $match->getResults()->getSetsVisitorsTotal();
        }
        $this->template->setsHome = $setsHome;
        $this->template->setsVisitors = $setsVisitors;
        $this->template->gamesHome = $gamesHome;
        $this->template->gamesVisitors = $gamesVisitors;
    }

    public function getPlaysMatches()
    {
        $this->players->setId(0);
        $this->matchesTableList->setSeasonYear(0);
        $this->matchesTableList->setPlay($this->playsTableList);
        $this->matchesTableList->setPlayer($this->players);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matches = $this->matchesTableList->getMatchesTableList();
        $this->calcPlayStats();
    }
    
}