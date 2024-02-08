<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\CmsEntity\ArticlesTable;
use App\Module\ErrorMailer;
use Nette\Utils\DateTime;
use App\Model\Entity\CmsEntity\ImagesList;

// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání článků
class ArticleForm extends BasicForms {

    private $article,
            $images,
            $mailer;

    public function __construct(ArticlesTable $article, ImagesList $images, ErrorMailer $mailer) {
        $this->article = $article;
        $this->mailer = $mailer;
        $this->images = $images;
    }

    protected function start() {
        if (is_null($this->article->getTitle())) {
            $this->article->setId($this->presenter->getParameter('id'));
            $this->article->calcArticlesTable();
        }
    }

    public function getImagesList() {
        $this->images->calcImagesList(true);
    }
    
    public function setImagesAssoc($images) { // převede získaný seznam obrázků z databáze na asoc. pole formát "id_obrazek" => "nazev"
        $imageArray = array();
        foreach ($images as $image) {
            $imageArray[$image->getId()] = \Nette\Utils\Html::el()->setText($image->getFilename())->data('img-src', $this->presenter->getHttpRequest()->getUrl()->getBasePath() . 'images/uploaded/' . $image->getUploadYear() . '/thumbnails/'. $image->getFilename());
        }

        return $imageArray;
    }

    public function create($operation, $presenter, $article = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($article)) {
            $this->article = $article;
        }
        $this->getImagesList();
        $form = new Form();
        $form->addText('rocnik', 'Ročník')
                ->setRequired('Zadejte ročník')
                ->setHtmlType('number')
                ->addRule(Form::RANGE, 'Lze zadat pouze ročník v rozmezí %d - %d', [2010, 2100]);
        $form->addText('titulek', 'Titulek článku')
                ->setRequired('Zadejte titulek článku')
                ->setMaxLength(50);
        $form->addText('uryvek', 'Úryvek článku')
                ->setRequired('Zadejte krátký úryvek článku, který se bude zobrazovat na hlavní stránce')
                ->setMaxLength(200);
        $form->addTextArea('text', 'Plný text článku')
                ->setRequired('Zadejte plný text článku, který se bude zobrazovat v jeho detailu')
                ->setMaxLength(4000);
        $form->addSelect('id_obrazek','Obrázek',$this->setImagesAssoc($this->images->getImagesList()));
        $form->addCheckbox('aktivni', 'Článek aktivní');
        $form->addSubmit('articleButton', 'Uložit');
        if ($this->operation == 'update') {
            $this->start();
            $this->setDefaults($form);
            $form->onValidate[] = array($this, 'validateForm');
        }
        $form->onSuccess[] = array($this, $operation);
        return $form;
    }

    public function validateForm($form) {
        if (
                $form['rocnik']->getValue() == $this->article->getYear() &&
                $form['titulek']->getValue() == $this->article->getTitle() &&
                $form['uryvek']->getValue() == $this->article->getPreview() &&
                $form['text']->getValue() == $this->article->getFullText() &&
                $form['id_obrazek']->getValue() == $this->article->getImage()->getId() &&
                $form['aktivni']->getValue() == $this->article->getActive()
        ) {
            $form->addError('Ve formuláři úpravy daného klubu jste proti počátečnímu stavu neprovedli žádnou změnu');
        }
    }

    protected function setDefaults($form) {
        $form->setDefaults([
            'rocnik' => $this->article->getYear(),
            'titulek' => $this->article->getTitle(),
            'uryvek' => $this->article->getPreview(),
            'text' => $this->article->getFullText(),
            'id_obrazek' => $this->article->getImage()->getId(),
            'aktivni' => $this->article->getActive()
        ]);
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $values['id_editor'] = $this->presenter->getUser()->getIdentity()->getId();
            $values['editace'] = DateTime::from('now');
            $this->article->updateArticle($id, $values);
            $this->article->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě klubu. Klub stejného názvu, zkratky, nicku nebo jejich kombinace se již v databázi nacházi');
            $this->mailer->setMessage('Chyba - úprava článku', 'Nastala nespecifikovaná chyba při editaci článku - úpravě. Jednalo se o článek s id ' . $this->presenter->getParameter('id') . ', titulkem ' . $form['titulek']->getValue() . ', v sezoně ' . $form['rocnik']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Neznámá chyba při úpravě článku s id ' .  $this->presenter->getParameter('id') . '. Kontaktujte správce.', 'error');
            $this->presenter->redirect('Cms:clankyUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            //$form->addError('Chyba při úpravě klubu. Nespecifikovaný typ, kontaktujte správce');
            $this->mailer->setMessage('Chyba - úprava článku', 'Nastala nespecifikovaná chyba při editaci článku - úpravě. Jednalo se o článek s id ' . $this->presenter->getParameter('id') . ', titulkem ' . $form['titulek']->getValue() . ', v sezoně ' . $form['rocnik']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            $this->presenter->flashMessage('Chyba při úpravě článku. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Cms:clankyUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();
            $values['vytvoreni'] = $values['editace'] = DateTime::from('now');
            $values['id_tvurce'] = $values['id_editor'] = $this->presenter->getUser()->getIdentity()->getId();
            $this->article->insertArticle($values);
            $this->article->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání klubu. Klub stejného názvu, zkratky, nicku nebo jejich kombinace se již v databázi nacházi');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->mailer->setMessage('Chyba - vkládání článku', 'Nastala nespecifikovaná chyba při editaci článku - vkládání. Jednalo se o článek s titulkem ' . $form['titulek']->getValue() . ' v ročníku ' . $form['rocnik'] . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            $this->presenter->flashMessage('Neznámá chyba při vkládání článku - kontaktujte správce.', 'error');
            //$this->presenter->redirect('this',[$final_values]);
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání klubu. Nespecifikovaný typ, kontaktujte správce');
            $this->mailer->setMessage('Chyba - vkládání článku', 'Nastala nespecifikovaná chyba při editaci článku - vkládání. Jednalo se o článek s titulkem ' . $form['titulek']->getValue() . ' v ročníku ' . $form['rocnik'] . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();

            $this->presenter->flashMessage('Chyba při vkládání článku. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('this', [$final_values]);
        }
    }

}
