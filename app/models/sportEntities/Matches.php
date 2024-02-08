<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\SportEntity\Players;

/**
 * Description of Matches
 *
 * @author inComputer
 */
class Matches extends BasicEntity {

    private
            $id,
            $player,
            $play,
            $date,
            $setsHome,
            $setsVisitors,
            $winHome,
            $winVisitors,
            $lossDefaultHome,
            $lossDefaultVisitors,
            $retireHome,
            $retireVisitors,
            $matchMenOrder,
            $descriptions;

    public function setId($id) {
        $this->id = $id;
    }

    public function setPlayer(Players $player) {
        $this->player = $player;
    }

    public function setPlay(Plays $play) {
        $this->play = $play;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setSetsHome($setsHome) {
        $this->setsHome = $setsHome;
    }

    public function setSetsVisitors($setsVisitors) {
        $this->setsVisitors = $setsVisitors;
    }

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

    public function setRetireHome($retireHome) {
        $this->retireHome = $retireHome;
    }

    public function setRetireVisitors($retireVisitors) {
        $this->retireVisitors = $retireVisitors;
    }
    
    public function setMatchMenOrder($matchMenOrder){
        $this->matchMenOrder = $matchMenOrder;
    }

    public function setDescriptions($descriptions) {
        $this->descriptions = $descriptions;
    }

    public function getId() {
        return $this->id;
    }

    public function getPlayer() {
        return $this->player;
    }

    public function getPlay() {
        return $this->play;
    }

    public function getDate() {
        return $this->date;
    }

    public function getSetsHome() {
        return $this->setsHome;
    }

    public function getSetsVisitors() {
        return $this->setsVisitors;
    }

    public function getWinHome() {
        return $this->winHome;
    }

    public function getWinVisitors() {
        return $this->winVisitors;
    }
    
    public function getLossDefaultHome() {
        return $this->lossDefaultHome;
    }

    public function getLossDefaultVisitors() {
        return $this->lossDefaultVisitors;
    }

    public function getRetireHome() {
        return $this->retireHome;
    }

    public function getRetireVisitors() {
        return $this->retireVisitors;
    }
    
    public function getMatchMenOrder(){
        return $this->matchMenOrder;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function deleteMatch() {
        try {
            $this->eraseMatch();
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    private function eraseMatch() {
        return $this->database->query('DELETE FROM zapas WHERE id_zapas = ?', (int) $this->id)->fetch();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
