<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;

/**
 * Description of Plays
 *
 * @author inComputer
 */
class Plays extends BasicEntity {

    protected
            $id,
            $round,
            $season,
            $matchesHome,
            $matchesVisitors,
            $winHome,
            $winVisitors,
            $lossDefaultHome,
            $lossDefaultVisitors,
            $date,
            /* $dateAlternative, */
            $descriptions;

    public function setId($id) {
        $this->id = $id;
    }

    public function setRound(Rounds $round) {
        $this->round = $round;
    }

    public function setSeason($season) {
        $this->season = $season;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    /*
      public function setDateAlternative($dateAlternative) {
      $this->dateAlternative = $dateAlternative;
      }
     * 
     */

    public function setWinHome($winHome) {
        $this->winHome = $winHome;
    }

    public function setWinVisitors($winVisitors) {
        $this->winVisitors = $winVisitors;
    }

    public function setLossDefaultHome($lossDefaultHome) {
        $this->lossDefaultHome = $lossDefaultHome;
    }

    public function setLossDefaultVisitors($lossDefaultVisitors) {
        $this->lossDefaultVisitors = $lossDefaultVisitors;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function getId() {
        return $this->id;
    }

    public function getRound() {
        return $this->round;
    }

    public function getSeason() {
        return $this->season;
    }

    public function getWinHome() {
        return $this->winHome;
    }

    public function getWinVisitors() {
        return $this->winVisitors;
    }

    public function getDate() {
        return $this->date;
    }

    /*
      public function getDateAlternative() {
      return $this->dateAlternative;
      }
     *
     */

    public function getLossDefaultHome() {
        return $this->lossDefaultHome;
    }

    public function getLossDefaultVisitors() {
        return $this->lossDefaultVisitors;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

    protected function setPlay($playData) {
//        if ($playData) {
//            $this->id = isset($playData->id_utkani) ? $playData->id_utkani : NULL;
//            $this->round = isset($playData->kolo) ? $playData->kolo : NULL;
//            $this->sex = isset($playData->pohlavi) ? $playData->pohlavi : NULL;
//            $this->birthNumber = isset($playData->rok_narozeni) && $playData->rok_narozeni != '' ? $playData->rok_narozeni : NULL;
//            $this->hand = isset($playData->ruka) ? $playData->ruka : NULL;
//            $this->height = isset($playData->vyska) ? $playData->vyska : NULL;
//            $this->weight = isset($playData->vaha) ? $playData->vaha : NULL;
//            $this->descriptions = isset($playData->hrac_info) && $playData->hrac_info != '' ? $playData->hrac_info : NULL;
//        }
    }

    public function deletePlay() {
        try {
            $this->erasePlay();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function calcPlay() {
        try {
            $values = $this->readPlay();
            $this->setPlay($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    private function readPlay() {
        return $this->database->query('SELECT * FROM utkani WHERE id_utkani = ?', (int) $this->id)->fetch();
    }

    private function erasePlay() {
        return $this->database->query('DELETE FROM utkani WHERE id_utkani = ?', (int) $this->id)->fetch();
    }

}
