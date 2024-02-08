<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CmsEntity;

use App\Model\Entity\CmsEntity\ArticlesTable;

/**
 * Description of Matches
 *
 * @author inComputer
 */
class ArticlesTableList extends ArticlesTable {

    private
            $articlesTableList = array(),
            $articlesPageLength = 4,
            $articlesPageNumber,
            $articlesTableListCount;

    public function setArticlesPageLength($articlesPageLength) {
        $this->articlesPageLength = $articlesPageLength;
    }

    public function setArticlesPageNumber($articlesPageNumber) {
        $this->articlesPageNumber = $articlesPageNumber;
    }

    public function getArticlesPageLength() {
        return $this->articlesPageLength;
    }

    public function getArticlesPageNumber() {
        return $this->articlesPageNumber;
    }
    
    public function setArticlesTableListCount($articlesTableListCount)
    {
        $this->articlesTableListCount = $articlesTableListCount->count;
    }
    
    public function getArticlesTableListCount()
    {
        return $this->articlesTableListCount;
    }

    protected function setArticlesTableList($articleData) {
        foreach ($articleData as $article) {
            $newArticle = new ArticlesTable($this->database);
            $newArticle->setArticlesTable($article);
            $this->articlesTableList[] = $newArticle;
        }
    }

    protected function readArticlesTableList($onlyActiveArticles) {
        return $this->database->query('SELECT * FROM cms.clanky_vypis(?,?,?,?,?,?)', !is_null($this->id) ? (int) $this->id : 0, !is_null($this->getYear()) ? (int) $this->getYear() : 0, !is_null($this->getSlug()) ? $this->getSlug() : '', !is_null($this->getArticlesPageLength()) ? (int) $this->getArticlesPageLength() : 0, !is_null($this->getArticlesPageNumber()) ? (int) $this->getArticlesPageNumber() : 0,$onlyActiveArticles)->fetchAll();
    }
    
    private function readActiveArticlesTableListCount()
    {
        return $this->database->query('SELECT COUNT(id_clanek) FROM cms.clanek WHERE rocnik = ? AND aktivni = TRUE', !is_null($this->getYear()) ? (int) $this->getYear() : 0)->fetch();
    }

    public function calcActiveArticlesTableListCount()
    {
        try {
            $value = $this->readActiveArticlesTableListCount();
            $this->setArticlesTableListCount($value);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }
    
    public function calcArticlesTableList($onlyActiveArticles) {
        try {
            $values = $this->readArticlesTableList($onlyActiveArticles);
            $this->setArticlesTableList($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function getArticlesTableList() {
        return $this->articlesTableList;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
