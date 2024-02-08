<?php

//namespace App\Form\SportForm;

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\CmsEntity\ArticlesTable;
use App\Module\ErrorMailer;

class GalleryForm extends BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání článků
{  
    private $article;
    private $mailer;
    
    public function __construct(ArticlesTable $article, ErrorMailer $mailer) {
        $this->article = $article;
        $this->mailer = $mailer;
    }
    
    protected function start()
    {
        if (is_null($this->article->getTitle())) {
            $this->article->setId($this->presenter->getParameter('id'));
            $this->article->calcArticlesTable();
        }
    }
    
    public function create($operation, $presenter, $article = null) // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
    {
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($article)) {
            $this->article = $article;
        }
        $form = new Form();
        $form->addText('nazev', 'Název klubu')
                ->setRequired('Zadejte název klubu')
                ->setMaxLength(50)
                ->addRule(Form::PATTERN, 'Zadejte název klubu ve správném tvaru (začíná velkým písmenem)', '[A-Z ĚŠČŘŽÝÁÍÉÚŮ]{1}.*');
        $form->addText('zkratka', 'Zkrácený název')
                ->setRequired('Zadejte zkrácený název klubu - bude se zobrazovat v tabulkách')
                ->setMaxLength(15)
                ->addRule(Form::PATTERN, 'Zadejte zkrácený název ve správném tvaru (začíná velkým písmenem a je max. 15 znaků dlouhý)', '[A-Z ĚŠČŘŽÝÁÍÉÚŮ]{1}.*');
        $form->addText('nick', 'Nick klubu')
                ->setRequired('Zadejte nick klubu - bude se zobrazovat v minitabulkách')
                ->setMaxLength(3)
                ->addRule(Form::PATTERN, 'Zadejte nick pro minitabulku - přesně 3 znaky (pouze velká písmena bez diakritiky)', '[A-Z]{3}');
        $form->addText('adresa', 'Adresa klubu')
                ->setRequired(false)
                ->setMaxLength(50);             
        $form->addTextArea('klub_info', 'Info o klubu')
                ->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setRequired(false)
                ->setMaxLength(50)
                ->setAttribute('rows', 3);
        $form->addSubmit('clubButton', 'Uložit');
        if ($this->operation == 'update'){
        $this->start();
        $this->setDefaults($form);
        $form->onValidate[] = array($this, 'validateForm');
        }
        $form->onSuccess[] = array($this, $operation);
        return $form;
    }
    
    public function validateForm($form)
    {
        if ($form['nazev']->getValue() == $this->club->getName() &&
            $form['zkratka']->getValue() == $this->club->getShortcut() &&
            $form['nick']->getValue() == $this->club->getNick() &&
            $form['adresa']->getValue() == $this->club->getAddress() &&
            $form['klub_info']->getValue() == $this->club->getDescriptions()){
            $form->addError('Ve formuláři úpravy daného klubu jste proti počátečnímu stavu neprovedli žádnou změnu');
            }
    }
    
    protected function setDefaults($form) 
    {
        $form->setDefaults([
            'nazev' => $this->club->getName()   ,
            'zkratka' => $this->club->getShortcut(),
            'nick' => $this->club->getNick(),
            'adresa' => $this->club->getAddress(),
            'klub_info' => $this->club->getDescriptions()
            ]);
    }
   
    public function update($form) //provede se po odeslání vyplněného formuláře pro úpravu hráče
    {
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->club->updateClub($id, $values);
            $this->club->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě klubu. Klub stejného názvu, zkratky, nicku nebo jejich kombinace se již v databázi nacházi');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při úpravě klubu. Klub stejného názvu, zkratky, nicku nebo jejich kombinace se již v databázi nacházi.', 'error');
            $this->presenter->redirect('Sport:klubyUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            //$form->addError('Chyba při úpravě klubu. Nespecifikovaný typ, kontaktujte správce');
            $this->mailer->setMessage('Chyba - úprava klubu', 'Nastala nespecifikovaná chyba při editaci klubu - úpravě. Jednalo se o klub s id ' . $this->presenter->getParameter('id') . ', názvem ' . $form['nazev']->getValue() . ', zkratkou ' . $form['zkratka']->getValue() . ' a nickem ' . $form['nick']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při úpravě klubu. Nespecifikovaný typ, kontaktujte správce.', 'error');           
            $this->presenter->redirect('Sport:klubyUprava', $this->presenter->getParameter('id'));
        }       
    }
    
    public function insert($form) //provede se po odeslání vyplněného formuláře pro vložení nového hráče
    {
        try {
            $values = $form->getValues();
            $this->club->insertClub($values);     
            $this->club->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání klubu. Klub stejného názvu, zkratky, nicku nebo jejich kombinace se již v databázi nacházi');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při úpravě klubu. Klub stejného názvu, zkratky, nicku nebo jejich kombinace se již v databázi nacházi.', 'error');
            //$this->presenter->redirect('this',[$final_values]);
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání klubu. Nespecifikovaný typ, kontaktujte správce');
            $this->mailer->setMessage('Chyba - vkládání kluby', 'Nastala nespecifikovaná chyba při editaci klubu - vkládání. Jednalo se o klub s názvem ' . $form['nazev']->getValue() . ', zkratkou ' . $form['zkratka']->getValue() . ' a nickem ' . $form['nick']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při vkládání klubu. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('this', [$final_values]);
        }
    }        
}