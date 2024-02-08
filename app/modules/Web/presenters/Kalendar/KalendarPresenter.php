<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;

class KalendarPresenter extends BasicPresenter {

    private $ajax = false;

    public function beforeRender() {
        parent::beforeRender();
        $this->currentYear = $this->currentYear ?: $this->getCurrentYear();
        $this->currentMonth = $this->currentMonth ?: $this->getCurrentMonth();
        $this->template->year = $this->currentYear;
        $this->template->month = $this->currentMonth;
    }

    public function renderDefault() {
        if (!$this->ajax) {
            parent::renderDefault();
            $this->template->playsTable = $this->playsTableList->getPlaysTableList();
        } else {
            $this->getPlays();
        }
    }

    private function getPlays() {
        $this->playsTableList->setSeasonYear($this->currentYear);
        $this->playsTableList->calcPlaysTableList();
        $this->template->playsTable = $this->playsTableList->getPlaysTableList();
    }

    public function handleCalendarData($year, $month) { // handle na ajax událost (změna roku) v šabloně s detaily o hráči
        $this->currentYear = $year;
        $this->currentMonth = sprintf("%02d", $month);

        // pokud přijde ajax požadavek, překresli požadovanou oblast
        if ($this->isAjax()) {

            $this->ajax = true;
            $this->redrawControl('calendarDiv');
            $this->redrawControl('calendarScript');
        }
    }

}
