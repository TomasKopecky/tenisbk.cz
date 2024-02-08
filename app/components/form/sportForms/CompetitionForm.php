<?php

//namespace App\Form\SportForm;

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\Competitions;
use App\Module\ErrorMailer;

class CompetitionForm extends BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání klubů
{  
    private $competition,
            $mailer;
    
    public function __construct(Competitions $competition, ErrorMailer $mailer) {
        $this->competition = $competition;
        $this->mailer = $mailer;
    }


    protected function start()
    {
               if (is_null($this->competition->getName())) {
            $this->competition->setId($this->presenter->getParameter('id'));
            $this->competition->calcCompetitions();
        }
    }
    
    public function create($operation, $presenter, $competition = null) // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
    {
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($competition)) {
            $this->competition = $competition;
        }
        $form = new Form();
        $form->addText('jmeno', 'Název soutěže')
                ->setRequired('Zadejte název soutěže')
                ->setMaxLength(50);
        $form->addText('spravce_souteze', 'Jméno správce soutěže')
                ->setRequired(false)
                ->setMaxLength(50)
                ->addRule(Form::PATTERN, 'Zadejte jméno a příjmení správce (pouze dvouslovná jména, jako oddělovač použijte mezeru)',
                        '[A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ]{1}[a-z ěščřďťňžýáíéúů]+[ ]{1}[A-Z ĚŠČŘŤĎŇŽÝÁÍÉÚŮ]{1}[a-zA-Z-ěščřďťňžýáíéúůĚŠČŘŤĎŇŽÝÁÍÉÚŮ]+$');          
        $form->addTextArea('soutez_info', 'Info o soutěži')
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setRequired(false)
                ->setMaxLength(200)
                ->setAttribute('rows', 3);
        $form->addSubmit('competitionButton', 'Uložit');
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
        if ($form['jmeno']->getValue() == $this->competition->getName() &&
            $form['spravce_souteze']->getValue() == $this->competition->getAdmin() &&
            $form['soutez_info']->getValue() == $this->competition->getDescriptions()){
                $form->addError('Ve formuláři jste neprovedli žádnou změnu');            
            }
    }
    
    protected function setDefaults($form) 
    {
        $form->setDefaults([
            'jmeno' => $this->competition->getName(),
            'spravce_souteze' => $this->competition->getAdmin(),
            'soutez_info' => $this->competition->getDescriptions()
        ]);
    }
   
    public function update($form) //provede se po odeslání vyplněného formuláře pro úpravu hráče
    {
        try {
            $values = $form->getValues();
            $this->competition->updateCompetition($this->presenter->getParameter('id'),$values);  // odkaz do modelu na provedení update v databázi
            $this->competition->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError('Chyba při úpravě soutěže. Soutěž stejného názvu se již v databázi nacházi.');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při úpravě soutěže. Soutěž stejného názvu se již v databázi nacházi.', 'error');
            $this->presenter->redirect('Sport:soutezeUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            //$form->addError('Chyba při úpravě soutěže. Nespecifikovaný typ, kontaktujte správce.');
            
            $this->mailer->setMessage('Chyba - úprava soutěže', 'Nastala nespecifikovaná chyba při editaci soutěže - úpravě. Jednalo se o soutěž s id ' . $this->presenter->getParameter('id') . ' a názvem ' . $form['jmeno']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při úpravě soutěže. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Sport:soutezeUprava', $this->presenter->getParameter('id'));
        }       
    }
    
    public function insert($form) //provede se po odeslání vyplněného formuláře pro vložení nového hráče
    {
        try {
            $values = $form->getValues();
            $this->competition->insertCompetition($values); 
            $this->competition->logInsert();
            } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání soutěže. Soutěž stejného názvu se již v databázi nacházi.');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při vkládání soutěže. Soutěž stejného názvu se již v databázi nacházi.', 'error');
            //$this->presenter->redirect('Sport:soutezeNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání soutěže. Nespecifikovaný typ, kontaktujte správce.');
            $this->mailer->setMessage('Chyba - vkládání soutěže', 'Nastala nespecifikovaná chyba při editaci soutěže - vkládání. Jednalo se o soutěž s názvem ' . $form['jmeno']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při vkládání soutěže. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:soutezeNova');
        }
    }        
}