<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\CompSystems;
use App\Model\Entity\SportEntity\CompetitionsList;
use App\Module\ErrorMailer;

class CompSystemForm extends BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání registrací {
{
    private $compSystem,
            $competitionsList,
            $mailer;

    public function __construct(CompSystems $compSystems, CompetitionsList $competitionsList, ErrorMailer $mailer) {
        $this->compSystem = $compSystems;
        $this->competitionsList = $competitionsList;
        $this->mailer;
    }

    public function start() {
        if (is_null($this->compSystem->getSeason())) {
            $this->compSystem->setId($this->presenter->getParameter('id'));
            $this->compSystem->calcCompSystem();
        }
    }
    
    public function getOptionsList()
    {
                $this->competitionsList->calcCompetitionsList();
    }

    public function create($operation, $presenter, $compSystem = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($compSystem)) {
            $this->compSystem = $compSystem;
        }
        $this->getOptionsList();
        $form = new Form();
        $form->addSelect('id_soutez', 'Soutěž', $this->setCompetitionsAssoc($this->competitionsList->getCompetitionsList()))
                ->setPrompt('Zvolte soutěž')
                ->setRequired('Je třeba vybrat soutěž pro systém');
        $form->addText('rocnik', 'Ročník')
                ->setRequired('Zadejte ročník')
        ->setHtmlType('number')
                ->addRule(Form::MIN, 'Nelze zadat dřívější rok než 2010',2010);
        $form->addText('system_kol', 'Systém kol')
                                ->setRequired('Zadejte herní systém kol')
                 ->setHtmlType('number')
                ->addRule(Form::MIN, 'Lze zadat pouze jednokolový či dvoukolový systém',1)
                ->addRule(Form::MAX, 'Lze zadat pouze jednokolový či dvoukolový systém',2);
        $form->addTextArea('soutez_system_info', 'Info o systému')
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(200);
        $form->addSubmit('compSystemButton', 'Uložit');
        if ($this->operation == 'update') {
                    $this->start();
            $this->setDefaults($form);
            $form->onValidate[] = [$this, 'validateForm'];
        }
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    public function validateForm($form) {
        if ($form['id_soutez']->getSelectedItem() == $this->compSystem->getCompetition()->getName() &&
                $form['rocnik']->getValue() == $this->compSystem->getSeason() &&
                $form['system_kol']->getValue() == $this->compSystem->getRoundSystem() &&
                $form['soutez_system_info']->getValue() == $this->compSystem->getDescriptions()) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
    }


    public function setCompetitionsAssoc($competitions) { // převede získanou tabulku hráčů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $competitionsArray = array();
        foreach ($competitions as $competition) {
            $competitionsArray[$competition->getId()] = $competition->getName();
        }

        return $competitionsArray;
    }

    protected function setDefaults($form) {
        $form->setDefaults([
            'id_soutez' => $this->compSystem->getCompetition()->getId(),
            'rocnik' => $this->compSystem->getSeason(),
            'system_kol' => $this->compSystem->getRoundSystem(),
            'soutez_system_info' => $this->compSystem->getDescriptions()
        ]);
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->compSystem->updateCompSystem($id, $values);
            $this->compSystem->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            $this->presenter->flashMessage('Chyba při úpravě systému. Zadaná soutěž má již pro zadaný ročník zvolen herní systém. Změny vráceny zpět.', 'error');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Sport:systemyUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->mailer->setMessage('Chyba - úprava systému soutěží', 'Nastala nespecifikovaná chyba při editaci systému soutěží - úpravě. Jednalo se o systém s id ' . $this->presenter->getParameter('id') . ', v ročníku ' . $form['rocnik']->getValue() . ', se systémem kol ' . $form['system_kol']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při úpravě systému. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Sport:systemyUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();
            $this->compSystem->insertCompSystem($values);
            $this->compSystem->logInsert();
        } catch (\Nette\Database\DriverException $e) {
                        $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný klub - ' . $form['id_klub']->getSelectedItem() . ' - je již ve zvoleném období v nějaké soutěži zaregistrován. Změny vráceny zpět');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při vkládání systému. Zadaná soutěž - ' . $form['id_soutez']->getSelectedItem() . ' - má již ve zvoleném ročníku stanoven herní systém. Změny vráceny zpět', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);            
//$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->mailer->setMessage('Chyba - vkládání systému soutěžeí', 'Nastala nespecifikovaná chyba při editaci systému soutěží - vkládání. Jednalo se o systém v ročníku ' . $form['rocnik']->getValue() . ', se systémem kol ' . $form['system_kol']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při vkládání systému. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
