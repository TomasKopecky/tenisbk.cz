<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CmsEntity;

use App\Model\Entity\CmsEntity\Articles;
use App\Model\Entity\UserEntity\Roles;

/**
 * Description of Matches
 *
 * @author inComputer
 */
class ArticlesTable extends Articles {

    private
            $authorRole,
            $editorRole;

    public function setAuthorRole(Roles $authorRole) {
        $this->authorRole = $authorRole;
    }

    public function setEditorRole(Roles $editorRole) {
        $this->editorRole = $editorRole;
    }

    public function getAuthorRole() {
        return $this->authorRole;
    }

    public function getEditorRole() {
        return $this->editorRole;
    }

    protected function readArticleTable() {
        return $this->database->query('SELECT * FROM cms.clanky_vypis(?,?,?)', !is_null($this->id) ? (int) $this->id : 0,!is_null($this->getYear()) ? (int) $this->getYear() : 0, !is_null($this->getSlug()) ? $this->getSlug() : '')->fetch();
    }

    protected function setArticlesTable($articleData) {
        if (!empty($articleData)) {
            $this->setArticle($articleData);
            $authorRole = new Roles($this->database);
            $editorRole = new Roles($this->database);
            $authorRole->setTitle($articleData->tvurce_role);
            $editorRole->setTitle($articleData->editor_role);
            $this->setAuthorRole($authorRole);
            $this->setEditorRole($editorRole);
        }
    }

    public function calcArticlesTable() {
        try {
            $values = $this->readArticleTable();
            $this->setArticlesTable($values);
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
