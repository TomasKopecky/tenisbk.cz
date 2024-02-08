<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\MatchesTableList;
use App\Model\Entity\SportEntity\PlaysTable;

class ZapasyPresenter extends BasicPresenter {

    private $matchesTableList,
            $playsTable;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, MatchesTableList $matchesTableList, PlaysTable $playsTable) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->matchesTableList = $matchesTableList;
        $this->playsTable = $playsTable;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault() {
        $this->redirect(':Web:Vysledky:'); // protože není sekce utkání bez id užívána, při přístupu do ní, přesměrujemem do sekce aktuality na homepage
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function renderDetail($id) {
        parent::renderDefault();
        $this->matchesTableList->setId($id);
        $this->matchesTableList->calcMatchesTable();

        if (is_null($this->matchesTableList->getClubHome())) {
            $this->flashMessage('Zápas daného ID v databázi neexistuje', 'error');
            $this->redirect(':Web:Vysledky:');
        }

        $this->getMatchData();
        $this->getPlayData($this->matchesTableList->getPlay()->getId());
    }
    
    public function getMatchData()
    {
        $this->template->match = $this->matchesTableList;
    }
    
    public function getPlayData($id)
    {
        $this->playsTable->setId($id);
        $this->playsTable->calcPlaysTable();
        $this->template->play = $this->playsTable;
    }

}
