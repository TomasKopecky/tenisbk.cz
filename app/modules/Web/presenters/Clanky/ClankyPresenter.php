<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use App\Model\Entity\SportEntity\ClubStatsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\CmsEntity\ArticlesTableList;

class ClankyPresenter extends BasicPresenter {
    
    private $articlesTableList;
    
    public function __construct(ClubStatsList $clubStatsList, PlaysTableList $playsTableList, ArticlesTableList $articlesTableList) {
        parent::__construct($clubStatsList, $playsTableList);
        $this->articlesTableList = $articlesTableList;
    }

    public function renderDefault() {
        $this->redirect(':Web:Homepage:default');
    }

    public function renderDetail($slug) {
        parent::renderDefault();
        //$this->articlesTableList->setId($id);
        $this->articlesTableList->setSlug($slug);
        $this->articlesTableList->calcArticlesTable();
            if (is_null($this->articlesTableList->getTitle())) {
                $this->flashMessage('Chybně zadaná URL adresa článku', 'error');
                $this->redirect(':Web:Homepage:default');
            }
            $this->getArticle();
    }
    
    private function getArticle()
    {
        $this->template->articlesTable = $this->articlesTableList;
    }

}
