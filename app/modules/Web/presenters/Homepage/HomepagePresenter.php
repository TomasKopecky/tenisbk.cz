<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\CmsEntity\ArticlesTableList;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;

class HomepagePresenter extends BasicPresenter {

    private $articlesTableList,
            $articlesPageNumber,
            $currentPage=1,
            $articlesYear,
            $ajax = false;

    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, ArticlesTableList $articlesTableList) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->articlesTableList = $articlesTableList;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->articlesPageNumber = $this->articlesPageNumber = $this->articlesPageNumber ?? 0;
        $this->template->currentPage = $this->currentPage;
    }

    public function renderDefault() {

        if (!$this->ajax) {
            parent::renderDefault();
        }
        
        $this->articlesTableList->calcActiveYears();
        $this->articlesYear = $this->template->selectedYear = $this->articlesYear ?? $this->currentYear;
        $this->articlesTableList->setYear($this->articlesYear);
        if (!in_array($this->currentYear, $this->articlesTableList->getActiveYears())) {
            $this->template->currentEmptyYear = $this->currentYear;
        }
        
        $this->articlesTableList->calcActiveArticlesTableListCount();
        $this->articlesTableList->setArticlesPageNumber($this->articlesPageNumber);
        $this->articlesTableList->calcArticlesTableList(true);
        $this->template->activeYears = $this->articlesTableList->getActiveYears();
        $this->template->articlesTableList = $this->articlesTableList->getArticlesTableList();
        $this->template->articlesTableListCount = $this->articlesTableList->getArticlesTableListCount();
        $this->template->articlesTableListPageLength = $this->articlesTableList->getArticlesPageLength();
    }

    public function handleArticlesPageNumber($articlesYear, $articlesPageNumber) { // handle na ajax událost (změna roku) v šabloně s detaily o hráči
        $this->articlesYear = $articlesYear;
        //if ($articlesPageNumber > 0) {
        $this->articlesPageNumber = $articlesPageNumber > 0 ? $articlesPageNumber - 1 : 0;
        $this->currentPage = $articlesPageNumber;
        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxArticlesList');
        }
        //}
    }

}
