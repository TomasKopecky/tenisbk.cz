<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CheckTools;

use App\Model\Entity\SportEntity\Results;

class ResultsCheck {

    private $result,
            $homeRetire,
            $visitorsRetire,
            $homeLossDefault,
            $visitorsLossDefault,
            $errors = array(),
            $retireErrors = array();

    public function __construct(Results $result) {
        $this->result = $result;
    }

    public function setHomeRetire($homeRetire) {
        $this->homeRetire = $homeRetire;
    }

    public function setVisitorsRetire($visitorsRetire) {
        $this->visitorsRetire = $visitorsRetire;
    }

    public function setHomeLossDefault($homeLossDefault) {
        $this->homeLossDefault = $homeLossDefault;
    }

    public function setVisitorsLossDefault($visitorsLossDefault) {
        $this->visitorsLossDefault = $visitorsLossDefault;
    }

    public function getHomeRetire() {
        return $this->homeRetire;
    }

    public function getVisitorsRetire() {
        return $this->visitorsRetire;
    }

    public function getHomeLossDefault() {
        return $this->homeLossDefault;
    }

    public function getVisitorsLossDefault() {
        return $this->visitorsLossDefault;
    }

    private function checkIfSet1IsSetCorrectly() {
        if (
                !($this->result->getSet1Home() == 7 && $this->result->getSet1Visitors() == 6) &&
                !($this->result->getSet1Home() == 6 && $this->result->getSet1Visitors() == 7) &&
                !($this->result->getSet1Home() == 7 && $this->result->getSet1Visitors() == 5) &&
                !($this->result->getSet1Home() == 5 && $this->result->getSet1Visitors() == 7) &&
                !($this->result->getSet1Home() == 6 && ($this->result->getSet1Visitors() >= 0 && $this->result->getSet1Visitors() <= 4)) &&
                !(($this->result->getSet1Home() >= 0 && $this->result->getSet1Home() <= 4) && $this->result->getSet1Visitors() == 6)
        ) {
            $this->errors[] = 'Nebyl korektně nastaven výsledek 1. setu';
            return false;
        }
        return true;
    }

    private function checkIfSet2IsSetCorrectly() {
        if (
                !($this->result->getSet2Home() == 7 && $this->result->getSet2Visitors() == 6) &&
                !($this->result->getSet2Home() == 6 && $this->result->getSet2Visitors() == 7) &&
                !($this->result->getSet2Home() == 7 && $this->result->getSet2Visitors() == 5) &&
                !($this->result->getSet2Home() == 5 && $this->result->getSet2Visitors() == 7) &&
                !($this->result->getSet2Home() == 6 && ($this->result->getSet2Visitors() >= 0 && $this->result->getSet2Visitors() <= 4)) &&
                !(($this->result->getSet2Home() >= 0 && $this->result->getSet2Home() <= 4) && $this->result->getSet2Visitors() == 6)
        ) {
            $this->errors[] = 'Nebyl korektně nastaven výsledek 2. setu';
            return false;
        }
        return true;
    }

    private function checkIfSet3IsSetCorrectly() {
        if (
                !($this->result->getSet3Home() == 7 && $this->result->getSet3Visitors() == 6) &&
                !($this->result->getSet3Home() == 6 && $this->result->getSet3Visitors() == 7) &&
                !($this->result->getSet3Home() == 7 && $this->result->getSet3Visitors() == 5) &&
                !($this->result->getSet3Home() == 5 && $this->result->getSet3Visitors() == 7) &&
                !($this->result->getSet3Home() == 6 && ($this->result->getSet3Visitors() >= 0 && $this->result->getSet3Visitors() <= 4)) &&
                !(($this->result->getSet3Home() >= 0 && $this->result->getSet3Home() <= 4) && $this->result->getSet3Visitors() == 6)
        ) {
            $this->errors[] = 'Nebyl korektně nastaven výsledek 3. setu';
            return false;
        }
        return true;
    }

    private function checkIfSet1IsSet() {
        if ($this->result->getSet1Home() == 0 && $this->result->getSet1Visitors() == 0) {
//$this->errors[] = 'Nebyl nastaven výsledek 1. setu';
            return false;
        }
        return true;
    }

    private function checkIfSet2IsSet() {
        if ($this->result->getSet2Home() == 0 && $this->result->getSet2Visitors() == 0) {
//$this->errors[] = 'Nebyl nastaven výsledek 2. setu';
            return false;
        }
        return true;
    }

    private function checkIfSet3IsSet() {
        if ($this->result->getSet3Home() == 0 && $this->result->getSet3Visitors() == 0) {
//$this->errors[] = 'Nebyl nastaven výsledek 3. setu';
            return false;
        }
        return true;
    }

    private function checkIfSet2IsSetWithoutSet1() {
        if (!$this->checkIfSet1IsSet() && $this->checkIfSet2IsSet()) {
            $this->errors[] = 'Nelze nastavit výsledek 2. setu bez nastavení výsledku 1. setu';
            return true;
        }
        return false;
    }

    private function checkIfSet3IsSetWithoutSet2() {
        if (!$this->checkIfSet2IsSet() && $this->checkIfSet3IsSet()) {
            $this->errors[] = 'Nelze nastavit výsledek 3. setu bez nastavení výsledku 2. setu';
            return true;
        }
        return false;
    }

    private function checkIfAllSetsAreSet() {
        $this->checkIfSet1IsSet();
        $this->checkIfSet2IsSet();
        $this->checkIfSet3IsSet();
    }

    private function checkIfSetsAreSetWithoutPrevious() {
        if ($this->checkIfSet2IsSetWithoutSet1() || $this->checkIfSet3IsSetWithoutSet2()) {
            return true;
        }
        return false;
    }

    public function calcSet1Winner() {
        if ($this->getHomeRetire() || $this->getVisitorsRetire()) {
            if ($this->checkIfSet1IsSetCorrectly() || ($this->checkIfSet1IsSetCorrectly() && $this->checkIfTb1HasToBeSet() && $this->checkIfTb1IsSetCorrectly())) {
                return $this->result->getSet1Home() > $this->result->getSet1Visitors() ? 1 : 2;
            }
        } else {
            if ($this->result->getSet1Home() == 0 && $this->result->getSet1Visitors() == 0) {
                return null;
            }
            return $this->result->getSet1Home() > $this->result->getSet1Visitors() ? 1 : 2;
        }
    }

    public function calcSet2Winner() {
        if ($this->getHomeRetire() || $this->getVisitorsRetire()) {
            if ($this->checkIfSet2IsSetCorrectly() || ($this->checkIfSet2IsSetCorrectly() && $this->checkIfTb2HasToBeSet() && $this->checkIfTb2IsSetCorrectly())) {
                return $this->result->getSet2Home() > $this->result->getSet2Visitors() ? 1 : 2;
            }
        } else {
            if ($this->result->getSet2Home() == 0 && $this->result->getSet2Visitors() == 0) {
                return null;
            }
            return $this->result->getSet2Home() > $this->result->getSet2Visitors() ? 1 : 2;
        }
    }

    public function calcSet3Winner() {
        if ($this->getHomeRetire() || $this->getVisitorsRetire()) {
            if ($this->checkIfSet3IsSetCorrectly() || ($this->checkIfSet3IsSetCorrectly() && $this->checkIfTb3HasToBeSet() && $this->checkIfTb3IsSetCorrectly())) {
                return $this->result->getSet3Home() > $this->result->getSet3Visitors() ? 1 : 2;
            }
        } else {
            if ($this->result->getSet3Home() == 0 && $this->result->getSet3Visitors() == 0) {
                return null;
            }
            return $this->result->getSet3Home() > $this->result->getSet3Visitors() ? 1 : 2;
        }
    }

    public function calcWinHome() {
        if ($this->getHomeRetire()) {
            return 0;
        }
        if ($this->getVisitorsRetire()) {
            return 1;
        } else if ($this->calcMatchWinner() == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    public function calcWinVisitors() {
        if ($this->getVisitorsRetire()) {
            return 0;
        }
        if ($this->getHomeRetire()) {
            return 1;
        } else if ($this->calcMatchWinner() == 2) {
            return 1;
        } else {
            return 0;
        }
    }

    public function calcMatchWinner() {
        if (!$this->checkIfSet3HasToBePlayed()) {
            return $this->calcSet1Winner() == $this->calcSet2Winner() ? $this->calcSet1Winner() : null;
        } else {
            $winnersArray = array($this->calcSet1Winner(), $this->calcSet2Winner(), $this->calcSet3Winner() ?? 0);
            $countOccurrence = array_count_values($winnersArray);
            arsort($countOccurrence);
            return key($countOccurrence);
        }
    }

    private function checkIfSet3HasToBePlayed() {
        if (
                ($this->calcSet1Winner() == 1 && $this->calcSet2Winner() == 2) ||
                ($this->calcSet1Winner() == 2 && $this->calcSet2Winner() == 1)
        ) {
            return true;
        }
        return false;
    }

    private function checkIfSetsAreSetCorrectly() {
        if (
                ($this->checkIfSet1IsSetCorrectly() && $this->checkIfSet2IsSetCorrectly() && !$this->checkIfSet3HasToBePlayed()) ||
                ($this->checkIfSet1IsSetCorrectly() && $this->checkIfSet2IsSetCorrectly() && $this->checkIfSet3IsSetCorrectly())
        ) {

            return true;
        }
        return false;
    }

    private function checkIfTb1IsSetCorrectly() {
        if (
                ($this->calcSet1Winner() == 1 &&
                !($this->result->getTb1Home() == 7 && ($this->result->getTb1Visitors() >= 0 && $this->result->getTb1Visitors() <= 5)) &&
                !($this->result->getTb1Home() > 5 && $this->result->getTb1Visitors() > 5 && $this->result->getTb1Home() - 2 == $this->result->getTb1Visitors())
                ) ||
                ($this->calcSet1Winner() == 2 &&
                !(($this->result->getTb1Home() >= 0 && $this->result->getTb1Home() <= 5) && $this->result->getTb1Visitors() == 7) &&
                !($this->result->getTb1Home() > 5 && $this->result->getTb1Visitors() > 5 && $this->result->getTb1Visitors() - 2 == $this->result->getTb1Home())
                )
        ) {
            $this->errors[] = 'Nebyl korektně nastaven výsledek 1. tie-breaku';
            return false;
        }
        return true;
    }

    private function checkIfTb2IsSetCorrectly() {
        if (
                ($this->calcSet2Winner() == 1 &&
                !($this->result->getTb2Home() == 7 && ($this->result->getTb2Visitors() >= 0 && $this->result->getTb2Visitors() <= 5)) &&
                !($this->result->getTb2Home() > 5 && $this->result->getTb2Visitors() > 5 && $this->result->getTb2Home() - 2 == $this->result->getTb2Visitors())
                ) ||
                ($this->calcSet2Winner() == 2 &&
                !(($this->result->getTb2Home() >= 0 && $this->result->getTb2Home() <= 5) && $this->result->getTb2Visitors() == 7) &&
                !($this->result->getTb2Home() > 5 && $this->result->getTb2Visitors() > 5 && $this->result->getTb2Visitors() - 2 == $this->result->getTb2Home())
                )
        ) {
            $this->errors[] = 'Nebyl korektně nastaven výsledek 2. tie-breaku';
            return false;
        }
        return true;
    }

    private function checkIfTb3IsSetCorrectly() {
        if (
                ($this->calcSet3Winner() == 1 &&
                !($this->result->getTb3Home() == 7 && ($this->result->getTb3Visitors() >= 0 && $this->result->getTb3Visitors() <= 5)) &&
                !($this->result->getTb3Home() > 5 && $this->result->getTb3Visitors() > 5 && $this->result->getTb3Home() - 2 == $this->result->getTb3Visitors())
                ) ||
                ($this->calcSet3Winner() == 2 &&
                !(($this->result->getTb3Home() >= 0 && $this->result->getTb3Home() <= 5) && $this->result->getTb3Visitors() == 7) &&
                !($this->result->getTb3Home() > 5 && $this->result->getTb3Visitors() > 5 && $this->result->getTb3Visitors() - 2 == $this->result->getTb3Home())
                )
        ) {
            $this->errors[] = 'Nebyl korektně nastaven výsledek 3. tie-breaku';
            return false;
        }
        return true;
    }

    private function checkIfTb1IsSetRealistically() {
        if (
                !(
                !($this->result->getTb1Home() == 7 && ($this->result->getTb1Visitors() >= 0 && $this->result->getTb1Visitors() <= 5)) &&
                !($this->result->getTb1Home() > 5 && $this->result->getTb1Visitors() > 5 && $this->result->getTb1Home() - 2 == $this->result->getTb1Visitors())
                ) ||
                !(
                !(($this->result->getTb1Home() >= 0 && $this->result->getTb1Home() <= 5) && $this->result->getTb1Visitors() == 7) &&
                !($this->result->getTb1Home() > 5 && $this->result->getTb1Visitors() > 5 && $this->result->getTb1Visitors() - 2 == $this->result->getTb1Home())
                )
        ) {
            $this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 1. setu - za stavu 6:6 nemůže mít tie-break konečný stav';
            return false;
        }
        if (
                (($this->result->getTb1Home() > 6 && $this->result->getTb1Visitors() == 0) || ($this->result->getTb1Home() == 0 && $this->result->getTb1Visitors() > 6)) ||
                (($this->result->getTb1Home() == 7 && $this->result->getTb1Visitors() == 6) || ($this->result->getTb1Home() == 6 && $this->result->getTb1Visitors() == 7)) ||
                (($this->result->getTb1Home() >= 7 && $this->result->getTb1Visitors() > $this->result->getTb1Home() + 1) || ($this->result->getTb1Visitors() >= 7 && $this->result->getTb1Home() > $this->result->getTb1Visitors() + 1)) ||
                (($this->result->getTb1Home() >= 7 && $this->result->getTb1Visitors() < $this->result->getTb1Home() - 1) || ($this->result->getTb1Visitors() >= 7 && $this->result->getTb1Home() < $this->result->getTb1Visitors() - 1))
        //(($this->result->getTb1Home() > 7 && $this->result->getTb1Visitors() < $this->result->getTb1Home() - 1) || ($this->result->getTb1Home() < 7 && $this->result->getTb1Visitors() > $this->result->getTb1Home() - 1))
        ) {
            $this->retireErrors[] = 'Není nastaven reálný výsledek tiebreaku 1. setu při zatrhnuté skreči';
            return false;
        }
        return true;
    }

    private function checkIfTb2IsSetRealistically() {
        if (
                !(
                !($this->result->getTb2Home() == 7 && ($this->result->getTb2Visitors() >= 0 && $this->result->getTb2Visitors() <= 5)) &&
                !($this->result->getTb2Home() > 5 && $this->result->getTb2Visitors() > 5 && $this->result->getTb2Home() - 2 == $this->result->getTb2Visitors())
                ) ||
                !(
                !(($this->result->getTb2Home() >= 0 && $this->result->getTb2Home() <= 5) && $this->result->getTb2Visitors() == 7) &&
                !($this->result->getTb2Home() > 5 && $this->result->getTb2Visitors() > 5 && $this->result->getTb2Visitors() - 2 == $this->result->getTb2Home())
                )
        ) {
            $this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 2. setu - za stavu 6:6 nemůže mít tie-break konečný stav';
            return false;
        }
        if (
                (($this->result->getTb2Home() > 6 && $this->result->getTb2Visitors() == 0) || ($this->result->getTb2Home() == 0 && $this->result->getTb2Visitors() > 6)) ||
                (($this->result->getTb2Home() == 7 && $this->result->getTb2Visitors() == 6) || ($this->result->getTb2Home() == 6 && $this->result->getTb2Visitors() == 7)) ||
                (($this->result->getTb2Home() >= 7 && $this->result->getTb2Visitors() > $this->result->getTb2Home() + 1) || ($this->result->getTb2Visitors() >= 7 && $this->result->getTb2Home() > $this->result->getTb2Visitors() + 1)) ||
                (($this->result->getTb2Home() >= 7 && $this->result->getTb2Visitors() < $this->result->getTb2Home() - 1) || ($this->result->getTb2Visitors() >= 7 && $this->result->getTb2Home() < $this->result->getTb2Visitors() - 1))
        //(($this->result->getTb2Home() > 7 && $this->result->getTb2Visitors() < $this->result->getTb2Home() - 1) || ($this->result->getTb2Home() < 7 && $this->result->getTb2Visitors() > $this->result->getTb2Home() - 1))
        ) {
            $this->retireErrors[] = 'Není nastaven reálný výsledek tiebreaku 2. setu při zatrhnuté skreči';
            return false;
        }
        return true;
    }

    private function checkIfTb3IsSetRealistically() {
        if (
                !(
                !($this->result->getTb3Home() == 7 && ($this->result->getTb3Visitors() >= 0 && $this->result->getTb3Visitors() <= 5)) &&
                !($this->result->getTb3Home() > 5 && $this->result->getTb3Visitors() > 5 && $this->result->getTb3Home() - 2 == $this->result->getTb3Visitors())
                ) ||
                !(
                !(($this->result->getTb3Home() >= 0 && $this->result->getTb3Home() <= 5) && $this->result->getTb3Visitors() == 7) &&
                !($this->result->getTb3Home() > 5 && $this->result->getTb3Visitors() > 5 && $this->result->getTb3Visitors() - 2 == $this->result->getTb3Home())
                )
        ) {
            $this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 3. setu - za stavu 6:6 nemůže mít tie-break konečný stav';
            return false;
        }
        if (
                (($this->result->getTb3Home() > 6 && $this->result->getTb3Visitors() == 0) || ($this->result->getTb3Home() == 0 && $this->result->getTb3Visitors() > 6)) ||
                (($this->result->getTb3Home() == 7 && $this->result->getTb3Visitors() == 6) || ($this->result->getTb3Home() == 6 && $this->result->getTb3Visitors() == 7)) ||
                (($this->result->getTb3Home() >= 7 && $this->result->getTb3Visitors() > $this->result->getTb3Home() + 1) || ($this->result->getTb3Visitors() >= 7 && $this->result->getTb3Home() > $this->result->getTb3Visitors() + 1)) ||
                (($this->result->getTb3Home() >= 7 && $this->result->getTb3Visitors() < $this->result->getTb3Home() - 1) || ($this->result->getTb3Visitors() >= 7 && $this->result->getTb3Home() < $this->result->getTb3Visitors() - 1))
        //(($this->result->getTb3Home() > 7 && $this->result->getTb3Visitors() < $this->result->getTb3Home() - 1) || ($this->result->getTb3Home() < 7 && $this->result->getTb3Visitors() > $this->result->getTb3Home() - 1))
        ) {
            $this->retireErrors[] = 'Není nastaven reálný výsledek tiebreaku 3. setu při zatrhnuté skreči';
            return false;
        }
        return true;
    }

    private function checkIfTbsAreSetCorrectly() {
        $tbs = array();
        if ($this->checkIfTb1HasToBeSet()) {
            $tbs[] = $this->checkIfTb1IsSetCorrectly();
        }
        if ($this->checkIfTb2HasToBeSet()) {
            $tbs[] = $this->checkIfTb2IsSetCorrectly();
        }
        if ($this->checkIfTb3HasToBeSet()) {
            $tbs[] = $this->checkIfTb3IsSetCorrectly();
        }

        return empty($tbs) ? true : (in_array(false, $tbs) ? false : true); // když je tam nějaký false, tak false, jinak true :);
    }

    private function checkIfTb1HasToBeSet() {
        if (
                ($this->result->getSet1Home() == 6 && $this->result->getSet1Visitors() == 7) ||
                ($this->result->getSet1Home() == 7 && $this->result->getSet1Visitors() == 6)
        ) {
            return true;
        }
        return false;
    }

    private function checkIfTb2HasToBeSet() {
        if (
                ($this->result->getSet2Home() == 6 && $this->result->getSet2Visitors() == 7) ||
                ($this->result->getSet2Home() == 7 && $this->result->getSet2Visitors() == 6)
        ) {
            return true;
        }
        return false;
    }

    private function checkIfTb3HasToBeSet() {
        if (
                ($this->result->getSet3Home() == 6 && $this->result->getSet3Visitors() == 7) ||
                ($this->result->getSet3Home() == 7 && $this->result->getSet3Visitors() == 6)
        ) {
            return true;
        }
        return false;
    }

    public function calcSetsWins() {
        $setsHome = 0;
        $setsVisitors = 0;
        $calculations = array($this->calcSet1Winner(), $this->calcSet2Winner(), $this->calcSet3Winner());
        foreach ($calculations as $calculation) {
            if ($calculation == 1) {
                $setsHome++;
            }
            if ($calculation == 2) {
                $setsVisitors++;
            }
        }
        $this->getHomeRetire() ? $setsVisitors = 2 : null;
        $this->getVisitorsRetire() ? $setsHome = 2 : null;
        return array($setsHome, $setsVisitors);
    }

    private function checkRetireIfSetsAreCompleted() {
        if ((!$this->checkIfSet1IsSetCorrectly()) && ($this->checkIfSet2IsSet() || $this->checkIfSet3IsSet())) {
            $this->retireErrors[] = 'Nekompletní nebo chybně nastavený 1. set - došlo v něm ke skreči? Pak je třeba zbylé sety nechat bez výsledku';
            return false;
        } else if ($this->checkIfSet1IsSetCorrectly() && !$this->checkIfSet2IsSetCorrectly() && $this->checkIfSet3IsSet()) {
            $this->retireErrors[] = 'Nekompletní nebo chybně nastavený 2. set - došlo v něm ke skreči? Pak je třeba zbylé sety nechat bez výsledku';
            return false;
        } else {
            return true;
        }
    }

    private function checkRetireIfSetsAreSetCorrectly() {
        if (($this->result->getSet1Home() > 7 || $this->result->getSet1Visitors() > 7)) {
            $this->retireErrors[] = 'Chybně nastavený výsledek 1. setu - nelze dosáhnout více jak 7 gamů v setu';
            return false;
        }
        if (($this->result->getSet2Home() > 7 || $this->result->getSet2Visitors() > 7)) {
            $this->retireErrors[] = 'Chybně nastavený výsledek 2. setu - nelze dosáhnout více jak 7 gamů v setu';
            return false;
        }
        if (($this->result->getSet3Home() > 7 || $this->result->getSet3Visitors() > 7)) {
            $this->retireErrors[] = 'Chybně nastavený výsledek 3. setu - nelze dosáhnout více jak 7 gamů v setu';
            return false;
        }
        if (($this->result->getSet1Home() == 7 || $this->result->getSet1Visitors() == 7)) {
            if (!$this->checkIfSet1IsSetCorrectly()) {
                $this->retireErrors[] = 'Chybně nastavený výsledek 1. setu - 7 gamů lze dosáhnout jen v rámci stavu 7:6, 7:5, 5:7 nebo 6:7';
                return false;
            }
            if ($this->checkIfTb1HasToBeSet() && !$this->checkIfTb1IsSetCorrectly()) {
                $this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 1. setu';
                return false;
            }
        }
        if (($this->result->getSet2Home() == 7 || $this->result->getSet2Visitors() == 7)) {
            if (!$this->checkIfSet2IsSetCorrectly()) {
                $this->retireErrors[] = 'Chybně nastavený výsledek 2. setu - 7 gamů lze dosáhnout jen v rámci stavu 7:6, 7:5, 5:7 nebo 6:7';
                return false;
            }
            if ($this->checkIfTb2HasToBeSet() && !$this->checkIfTb2IsSetCorrectly()) {
                $this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 2. setu';
                return false;
            }
        }
        if (($this->result->getSet3Home() == 7 || $this->result->getSet3Visitors() == 7)) {
            if ($this->checkIfTb3HasToBeSet() && !$this->checkIfSet3IsSetCorrectly()) {
                $this->retireErrors[] = 'Chybně nastavený výsledek 3. setu - 7 gamů lze dosáhnout jen v rámci stavu 7:6, 7:5, 5:7 nebo 6:7';
                return false;
            }
            if (!$this->checkIfTb3IsSetCorrectly()) {
                $this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 3. setu';
                return false;
            }
        }
        return true;
    }

    private function checkRetireIfTbsAreSetCorrectly() {
        if (($this->result->getSet1Home() == 6 && $this->result->getSet1Visitors() == 6)) {
            if (!$this->checkIfTb1IsSetRealistically()) {
                //$this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 3. setu';
                return false;
            }
        }
        if (($this->result->getSet2Home() == 6 && $this->result->getSet2Visitors() == 6)) {
            if (!$this->checkIfTb2IsSetRealistically()) {
                //$this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 3. setu';
                return false;
            }
        }
        if (($this->result->getSet3Home() == 6 && $this->result->getSet3Visitors() == 6)) {
            if (!$this->checkIfTb3IsSetRealistically()) {
                //$this->retireErrors[] = 'Chybně nastavený výsledek tie-breaku 3. setu';
                return false;
            }
        }
        return true;
    }

    private function retirementFullCheck() {
        if ($this->checkRetireIfSetsAreCompleted() && $this->checkRetireIfSetsAreSetCorrectly() && $this->checkRetireIfTbsAreSetCorrectly()) {
            return true;
        }
        return false;
    }

    public function matchRetireCheck() {
        if ($this->getHomeRetire() && $this->getVisitorsRetire()) {
            $this->errors[] = 'Nemůže být zvolena skreč ze strany obou soupeřů, skrečovat může vždy pouze jedna strana';
            return false;
        }
        $this->matchStandardCheck();
        if (empty($this->errors)) {
            $this->errors[] = 'Zatrhli jste, že došlo ke skreči zápasu, ale zadali jste kompletní platný výsledek. Takto nemůže dopadnout skrečovaný zápas';
            return false;
        }
        if ($this->retirementFullCheck()) {
            $this->errors = array();
            return true;
        }
        $this->errors = $this->retireErrors;
        return false;
    }

    private function matchStandardCheck() {
        if ($this->getHomeLossDefault() && $this->getVisitorsLossDefault()) {
            $this->errors[] = 'Nelze kontumovat zápas oběma stranami, kontumace musí proběhnout pouze ve prospěch jedné strany';
            return false;
        }
        $this->checkIfSetsAreSetWithoutPrevious();
        $this->checkIfSetsAreSetCorrectly();
        $this->checkIfTbsAreSetCorrectly();
        return true;
    }

    public function fullCheck() {
        if ($this->getHomeRetire() || $this->getVisitorsRetire()) {
            $this->matchRetireCheck();
        } else {
            $this->matchStandardCheck();
        }
        //$this->testValues();
    }

    private function testValues() {

        if ($this->checkIfSetsAreSetCorrectly()) {
            $this->errors[] = 'SETY OK';
        } else {
            $this->errors[] = 'SETY CHYBA';
        }
        if ($this->checkIfTbsAreSetCorrectly()) {
            $this->errors[] = 'TB OK';
        } else {
            $this->errors[] = 'TB CHYBA';
        }

        $this->errors[] = 'Vítěz 1. setu ' . $this->calcSet1Winner();
        $this->errors[] = 'Vítěz 2. setu ' . $this->calcSet2Winner();
        $this->errors[] = 'Vítěz 3. setu ' . $this->calcSet3Winner();
        $this->errors[] = 'Sety domácí ' . $this->calcSetsWins()[0];
        $this->errors[] = 'Sety hoste ' . $this->calcSetsWins()[1];
        $this->errors[] = 'Vítěz zápasu ' . ($this->calcMatchWinner() == 1 ? 'DOMÁCÍ' : 'HOSTÉ');
        $this->errors[] = $this->checkIfSet3HasToBePlayed() == true ? '3. set musí být hrán? ANO' : '3. set musí být hrán? NE';
    }

    public function getErrors() {
        return $this->errors;
    }

}
