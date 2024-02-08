<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Entity\CmsEntity;

use App\Model\Entity\BasicEntity;
use App\Model\Entity\UserEntity\Users;

/**
 * Description of Articles
 *
 * @author inComputer
 */
class Articles extends BasicEntity {

    protected
            $id,
            $year,
            $creationDate,
            $editDate,
            $title,
            $preview,
            $fullText,
            $author,
            $editor,
            $slug,
            $image,
            $active,
            $activeYears;

    public function setId($id) {
        $this->id = $id;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    public function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    public function setEditDate($editDate) {
        $this->editDate = $editDate;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setPreview($preview) {
        $this->preview = $preview;
    }

    public function setFullText($fullText) {
        $this->fullText = $fullText;
    }

    public function setAuthor(Users $author) {
        $this->author = $author;
    }

    public function setEditor(Users $editor) {
        $this->editor = $editor;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
    }

    public function setImage(Images $image) {
        $this->image = $image;
    }

    public function setActive($active) {
        $this->active = $active;
    }

    protected function setArticle($articleData) {
        if (!empty($articleData)) {
            $this->id = isset($articleData->id_clanek) ? $articleData->id_clanek : NULL;
            $this->setYear($articleData->rocnik);
            $this->creationDate = isset($articleData->vytvoreni) ? $articleData->vytvoreni : NULL;
            $this->editDate = isset($articleData->editace) ? $articleData->editace : NULL;
            $this->setTitle($articleData->titulek);
            $this->setPreview($articleData->uryvek);
            $this->setFullText($articleData->text);
            $author = new Users($this->database);
            $editor = new Users($this->database);
            $author->setName(isset($articleData->tvurce) ? $articleData->tvurce : NULL);
            $editor->setName(isset($articleData->editor) ? $articleData->editor : NULL);
            $author->setId(isset($articleData->id_tvurce) ? $articleData->id_tvurce : NULL);
            $editor->setId(isset($articleData->id_editor) ? $articleData->id_editor : NULL);
            $this->setEditor($editor);
            $this->setAuthor($author);
            $image = new Images($this->database);
            $this->slug = isset($articleData->clanek_slug) ? $articleData->clanek_slug : NULL;
            $image->setId(isset($articleData->id_obrazek) ? $articleData->id_obrazek : NULL);
            $image->setFilename(isset($articleData->obrazek_nazev) ? $articleData->obrazek_nazev : NULL);
            $image->setUploadYear(isset($articleData->obrazek_rok_nahrani) ? $articleData->obrazek_rok_nahrani : NULL);
            $this->setActive($articleData->aktivni);
            $this->setImage($image);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getYear() {
        return $this->year;
    }

    public function getCreationDate() {
        return $this->creationDate;
    }

    public function getEditDate() {
        return $this->editDate;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getPreview() {
        return $this->preview;
    }

    public function getFullText() {
        return $this->fullText;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getEditor() {
        return $this->editor;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getImage() {
        return $this->image;
    }

    public function getActive() {
        return $this->active;
    }
    
    public function getActiveYears() {
        return $this->activeYears;
    }

    public function getNewInstance() {
        return new self($this->database);
    }

    public function calcActiveYears() {
        try {
            $years = $this->readArticlesYears();
            $activeYears = array();
            //$activeYears[] = 0; // only when you would like to see all articles
            foreach ($years as $year) {
                $activeYears[] = $year->rocnik;
            }
            $this->activeYears = $activeYears;
        } catch (Nette\Neon\Exception $e) {
            return $e;
        }
    }

    public function insertArticle($articleData) {
        try {
            $this->setArticle($articleData);
            $this->createArticle();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function updateArticle($id, $articleData) {
        try {
            $this->setArticle($articleData);
            $this->setId($id);
            $this->editArticle();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function deleteArticle() {
        try {
            $this->eraseArticle();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    private function readArticlesYears() {
        return $this->database->query('SELECT distinct rocnik FROM cms.clanek ORDER BY rocnik asc')->fetchAll();
    }

    public function logInsert() {
        return $this->database->query('INSERT INTO log.log_clanek VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', NULL, (int) $this->seasonYear, $this->creationDate, $this->editDate, $this->title, $this->preview, $this->fullText, $this->author->getId(), $this->editor->getId(), $this->image->getId(), FALSE, TRUE, FALSE, FALSE)->fetch();
    }

    public function logUpdate() {
        return $this->database->query('INSERT INTO log.log_clanek VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, (int) $this->seasonYear, $this->creationDate, $this->editDate, $this->title, $this->preview, $this->fullText, $this->author->getId(), $this->editor->getId(), $this->image->getId(), FALSE, FALSE, TRUE, FALSE)->fetch();
    }

    public function logDelete() {
        return $this->database->query('INSERT INTO log.log_clanek VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', (int) $this->id, (int) $this->seasonYear, $this->creationDate, $this->editDate, $this->title, $this->preview, $this->fullText, $this->author->getId(), $this->editor->getId(), $this->image->getId(), FALSE, FALSE, TRUE, FALSE)->fetch();
    }

    private function createArticle() {
        return $this->database->query('SELECT * FROM cms.clanky_vkladani (?,?,?,?,?,?,?,?,?,?)', (int) $this->year, $this->creationDate, $this->editDate, $this->title, $this->preview, $this->fullText, $this->author->getId(), $this->editor->getId(), $this->image->getId(), $this->active)->fetch();
    }

    private function editArticle() {
        return $this->database->query('SELECT * FROM cms.clanky_uprava (?,?,?,?,?,?,?,?,?)', (int) $this->id, (int) $this->year, $this->editDate, $this->title, $this->preview, $this->fullText, $this->editor->getId(), $this->image->getId(), $this->active)->fetch();
    }

    private function eraseArticle() {
        return $this->database->query('DELETE FROM cms.clanek WHERE id_clanek = ?', (int) $this->id)->fetch();
    }
}
