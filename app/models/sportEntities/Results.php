<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

/**
 * Description of Results
 *
 * @author inComputer
 */
class Results {

    private
            $id,
            $setsHome,
            $setsVisitors,
            $set1Home,
            $set2Home,
            $set3Home,
            $set1Visitors,
            $set2Visitors,
            $set3Visitors,
            $tb1Home,
            $tb2Home,
            $tb3Home,
            $tb1Visitors,
            $tb2Visitors,
            $tb3Visitors;

    public function setFormResults($formResults) {
        $this->set1Home = (int) $formResults->set1_domaci;
        $this->set2Home = (int) $formResults->set2_domaci;
        $this->set3Home = (int) $formResults->set3_domaci;
        $this->set1Visitors = (int) $formResults->set1_hoste;
        $this->set2Visitors = (int) $formResults->set2_hoste;
        $this->set3Visitors = (int) $formResults->set3_hoste;
        $this->tb1Home = (int) $formResults->tb1_domaci;
        $this->tb2Home = (int) $formResults->tb2_domaci;
        $this->tb3Home = (int) $formResults->tb3_domaci;
        $this->tb1Visitors = (int) $formResults->tb1_hoste;
        $this->tb2Visitors = (int) $formResults->tb2_hoste;
        $this->tb3Visitors = (int) $formResults->tb3_hoste;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSetsHome($setsHome) {
        $this->setsHome = $setsHome;
    }

    public function setSetsVisitors($setsVisitors) {
        $this->setsVisitors = $setsVisitors;
    }

    public function setSet1Home($set1Home) {
        $this->set1Home = $set1Home;
    }

    public function setSet2Home($set2Home) {
        $this->set2Home = $set2Home;
    }

    public function setSet3Home($set3Home) {
        $this->set3Home = $set3Home;
    }

    public function setSet1Visitors($set1Visitors) {
        $this->set1Visitors = $set1Visitors;
    }

    public function setSet2Visitors($set2Visitors) {
        $this->set2Visitors = $set2Visitors;
    }

    public function setSet3Visitors($set3Visitors) {
        $this->set3Visitors = $set3Visitors;
    }

    public function setTb1Home($tb1Home) {
        $this->tb1Home = $tb1Home;
    }

    public function setTb2Home($tb2Home) {
        $this->tb2Home = $tb2Home;
    }

    public function setTb3Home($tb3Home) {
        $this->tb3Home = $tb3Home;
    }

    public function setTb1Visitors($tb1Visitors) {
        $this->tb1Visitors = $tb1Visitors;
    }

    public function setTb2Visitors($tb2Visitors) {
        $this->tb2Visitors = $tb2Visitors;
    }

    public function setTb3Visitors($tb3Visitors) {
        $this->tb3Visitors = $tb3Visitors;
    }

    public function getId() {
        return $this->id;
    }

    public function getSetsHome() {
        return $this->setsHome;
    }

    public function getSetsVisitors() {
        return $this->setsVisitors;
    }

    public function getSet1Home() {
        return $this->set1Home;
    }

    public function getSet2Home() {
        return $this->set2Home;
    }

    public function getSet3Home() {
        return $this->set3Home;
    }

    public function getSet1Visitors() {
        return $this->set1Visitors;
    }

    public function getSet2Visitors() {
        return $this->set2Visitors;
    }

    public function getSet3Visitors() {
        return $this->set3Visitors;
    }

    public function getSetsHomeTotal() {
        return $this->set1Home + $this->set2Home + $this->set3Home;
    }

    public function getSetsVisitorsTotal() {
        return $this->set1Visitors + $this->set2Visitors + $this->set3Visitors;
    }

    public function getSet1Full() {
        return $this->set1Home != 0 || $this->set1Visitors != 0 ? $this->set1Home . ':' . $this->set1Visitors : NULL;
    }

    public function getSet2Full() {
        return $this->set2Home != 0 || $this->set2Visitors != 0 ? $this->set2Home . ':' . $this->set2Visitors : NULL;
    }

    public function getSet3Full() {
        return $this->set3Home != 0 || $this->set3Visitors != 0 ? $this->set3Home . ':' . $this->set3Visitors : NULL;
    }

    public function getTb1Home() {
        return $this->tb1Home;
    }

    public function getTb2Home() {
        return $this->tb2Home;
    }

    public function getTb3Home() {
        return $this->tb3Home;
    }

    public function getTb1Visitors() {
        return $this->tb1Visitors;
    }

    public function getTb2Visitors() {
        return $this->tb2Visitors;
    }

    public function getTb3Visitors() {
        return $this->tb3Visitors;
    }
    
    public function getTb1Full() {
        return $this->tb1Home != 0 || $this->tb1Visitors != 0 ? $this->tb1Home . ':' . $this->tb1Visitors : NULL;
    }

    public function getTb2Full() {
        return $this->tb2Home != 0 || $this->tb2Visitors != 0 ? $this->tb2Home . ':' . $this->tb2Visitors : NULL;
    }

    public function getTb3Full() {
        return $this->tb3Home != 0 || $this->tb3Visitors != 0 ? $this->tb3Home . ':' . $this->tb3Visitors : NULL;
    }

    public function getFullSetsResult() {
        $resultString = '';
        if (!is_null($this->getSet1Full())) {
            $resultString .= $this->getSet1Full();
        }
        if (!is_null($this->getSet2Full())) {
            $resultString .= ', ' . $this->getSet2Full();
        }
        if (!is_null($this->getSet3Full())) {
            $resultString .= ', ' . $this->getSet3Full();
        }
        return $resultString;
    }
    
    public function getFullTbsResult() {
        $resultString = '';
        if (!is_null($this->getTb1Full())) {
            $resultString .= $this->getTb1Full();
        }
        if (!is_null($this->getTb2Full())) {
            $resultString .= ', ' . $this->getTb2Full();
        }
        if (!is_null($this->getTb3Full())) {
            $resultString .= ', ' . $this->getTb3Full();
        }
        return $resultString;
    }

}
