<?php

// hlavní presenter, ze kterého dědí všechny třídy presenteru, které vyžadují vykreslování minitabulek do šablon nebo např. přihlašovacího formuláře (poouze pro modul Web)

namespace Web\BasicPresenters;

use Nette\Application\UI;
use App\BasicLayoutPresenters\BasicPresenterLayout;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;

class BasicPresenter extends BasicPresenterLayout {

    protected
            $currentYear,
            $currentMonth,
            $currentDate,
            $clubStatsList,
            $playsTableList;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList) {
        parent::__construct();
        $this->clubStatsList = $clubStatsList;
        $this->playsTableList = $playsTableList;
    }

    protected function renderDefault() {
        $this->currentYear = $this->getCurrentYear(); // aktuální rok
        $this->currentDate = $this->getCurrentDate(); // aktuální datum
        $this->clubStatsList->setSeasonYear($this->getCurrentYear());
        $this->clubStatsList->calcClubStatsListMini();
        $this->playsTableList->setSeasonYear($this->getCurrentYear());
        $this->playsTableList->calcPlaysTableList();
        $this->calcMiniTables();
    }

    private function calcMiniTables() {
        $competitions = $plays = array();
        foreach ($this->clubStatsList->getClubStatsList() as $stat){
            $competitions[$stat->getCompetition()->getName()][]=$stat;
        }
        $this->template->minitableCompetitions = $competitions;
        foreach ($this->playsTableList->getPlaysTableList() as $play){
            $plays[$play->getCompetition()->getName()][]=($play);
        }
        $this->template->minitablePlays = $plays;
        //bdump($plays);
        //bdump($competitions);
    }

    protected function createComponentLoginForm() {
        $form = $this->loginFormFactory->create($this); // metoda create z továrny PlayerForm pro vytvoření přihlašovacího formuláře

        $form->onSuccess[] = function (UI\Form $form) {
            $this->redirect('default');
        };

        return $form;
    }

}
