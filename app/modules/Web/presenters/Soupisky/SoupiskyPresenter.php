<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\RegistrationsList;
use App\Model\Entity\SportEntity\ClubsList;

class SoupiskyPresenter extends BasicPresenter {

    private $registrationsList,
            $clubsList,
            $seasonYear;
    private $ajax = false;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, RegistrationsList $registrationsList, ClubsList $clubsList) {//Ranking $ranking) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->registrationsList = $registrationsList;
        $this->clubsList = $clubsList;
    }

    public function beforeRender() {
        parent::beforeRender();

        $this->template->selectedYear = $this->seasonYear = isset($this->seasonYear) ? $this->seasonYear : date('Y');
    }

    public function renderDefault() {
        if (!$this->ajax) {
            parent::renderDefault();
            $this->registrationsList->calcActiveYears();
            $this->template->registrations = $this->registrationsList;
            $this->clubsList->calcClubsList();
            $this->clubsList->setId(0);
            if (!in_array($this->seasonYear, $this->registrationsList->getActiveYears())) {
                $this->template->currentEmptyYear = $this->seasonYear;
            }
        }
        $this->registrationsList->setClub($this->clubsList);
            $this->registrationsList->setSeasonYear($this->seasonYear);
            $this->registrationsList->calcRegistrationsList(true);
            $this->template->registrationsList = $this->registrationsList->getRegistrationsList();
            $this->template->clubs = $this->clubsList->getClubsList();
    }

    public function handleRegistrations($seasonYear, $clubId) {
        $this->clubsList->setId($clubId);
        $this->seasonYear = $seasonYear;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxRegistrations');
        }
    }

}
