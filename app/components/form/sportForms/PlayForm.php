<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\PlaysTable;
use App\Model\Entity\SportEntity\ClubsList;
use App\Model\Entity\SportEntity\CompetitionsList;
use App\Module\ErrorMailer;

class PlayForm extends BasicForms {

    private $playsTable,
            $clubsList,
            $competitionsList,
            $year,
            $comp,
            $mailer;

    public function __construct(PlaysTable $playsTable, ClubsList $clubsList, CompetitionsList $competitionsList, ErrorMailer $mailer) {
        $this->playsTable = $playsTable;
        $this->clubsList = $clubsList;
        $this->competitionsList = $competitionsList;
        $this->mailer = $mailer;
    }

    public function start() {
        if (is_null($this->playsTable->getRound()) && $this->operation == 'update') {
            $this->playsTable->setId($this->presenter->getParameter('id'));
            $this->playsTable->calcPlaysTable();
            $this->comp = $this->playsTable->getCompetition()->getId();
            $this->year = $this->playsTable->getSeason();
        }
    }

    public function getCompList() {
        $this->competitionsList->calcCompetitionsList();
    }

    public function getClubList() {

        if (is_null($this->comp) && is_null($this->year)) {
            $this->clubsList->calcClubsList();
        } else {
            $this->clubsList->calcClubsListByYearAndComp($this->comp, $this->year);
        }
        //$this->clubsList->calcClubsList();
    }

    public function create($operation, $presenter, $playsTable = null, $year = null, $comp = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        $this->year = $year;
        $this->comp = $comp;
        if (!is_null($playsTable)) {
            $this->playsTable = $playsTable;
        }
        $this->start();
        $this->getClubList();
        $this->getCompList();
        $form = new Form();
        $form->addSelect('id_soutez', 'Soutěž', $this->setCompetitionsAssoc($this->competitionsList->getCompetitionsList()))
                ->setPrompt('Zvolte soutěž')
                ->setRequired('Je třeba vybrat soutěž pro zadání utkání');
        $form->addText('rocnik', 'Ročník')
                ->setRequired('Zadejte ročník')
                ->setHtmlType('number')
                ->addRule(Form::RANGE, 'Lze zadat pouze ročník v rozmezí %d - %d', [2010,2100]);
                //->addRule(Form::MAX, 'Zadejte ročník z 21. století', 2100);
        $form->addText('kolo', 'Herní kolo')
                ->setAttribute('class', 'form-control')
                ->setRequired('Zadejte kolo')
                ->setHtmlType('number')
                ->addRule(Form::MIN, 'Zadejte číslo kola od 1 do 10', 1)
                ->addRule(Form::MAX, 'Zadejte číslo kola od 1 do 10', 10);
        $form->addSelect('klub_domaci', 'Domácí klub', $this->setClubsAssoc($this->clubsList->getClubsList()))
                ->setAttribute('class', 'form-control')
                ->setPrompt('Zvolte domácí klub')
                ->setRequired('Zvolte domácí klub');
        $form->addSelect('klub_hoste', 'Hostující klub', $this->setClubsAssoc($this->clubsList->getClubsList()))
                ->setAttribute('class', 'form-control')
                ->setPrompt('Zvolte hostující klub')
                ->setRequired('Zvolte hostující klub')
                ->addRule(Form::NOT_EQUAL, 'Zadali jste stejné kluby', $form['klub_domaci']);
        /*
         * $form->addText('datum_plan', 'Datum plánované')
          ->setAttribute('class', 'form-control pull-right date')
          ->setHtmlAttribute('readonly')
          ->setRequired('Zvolte datum od');
          $form->addText('datum_nahradni', 'Datum náhradní')
          ->setAttribute('class', 'form-control pull-right date')
          ->setRequired(false)
          ->setHtmlAttribute('readonly');
         * 
         */
        $form->addCheckbox('utkani_kontumace_domaci')
                ->setAttribute('class', 'checkbox square');

        $form->addCheckbox('utkani_kontumace_hoste')
                ->setAttribute('class', 'checkbox square');

        $form->addText('utkani_datum', 'Datum utkání')
                ->setAttribute('class', 'form-control pull-right date')
                ->setHtmlAttribute('readonly')
                ->setRequired('Zvolte datum');
        $form->addTextArea('utkani_info', 'Info o utkání')
                ->setAttribute('class', 'form-control')
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(200);
        $form->addSubmit('playButton', 'Uložit');
        if ($this->operation == 'update') {


            $this->setDefaults($form);
            $form->onValidate[] = [$this, 'validateForm'];
        }
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    public function validateForm($form) {
        if (
                $form['rocnik']->getValue() == $this->playsTable->getSeason() &&
                $form['kolo']->getValue() == $this->playsTable->getRound()->getNumber() &&
                $form['utkani_kontumace_domaci']->getValue() == $this->playsTable->getLossDefaultHome() &&
                $form['utkani_kontumace_hoste']->getValue() == $this->playsTable->getLossDefaultVisitors() &&
                $form['klub_domaci']->getSelectedItem() == $this->playsTable->getClubHome()->getName() &&
                $form['klub_hoste']->getSelectedItem() == $this->playsTable->getClubVisitors()->getName() &&
                $form['utkani_datum']->getValue() == (is_null($this->playsTable->getDate()) ? null : date_format($this->playsTable->getDate(), "d.m.Y")) &&
                /* $form['datum_nahradni']->getValue() == (is_null($this->playsTable->getDateAlternative()) ? null : date_format($this->playsTable->getDateAlternative(), "d.m.Y")) && */
                $form['utkani_info']->getValue() == $this->playsTable->getDescriptions()) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
    }

    public function setClubsAssoc($clubs) { // převede získanou tabulku klubů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $clubArray = array();
        foreach ($clubs as $club) {
            $clubArray[$club->getId()] = $club->getName();
        }

        return $clubArray;
    }

    public function setCompetitionsAssoc($competitions) { // převede získanou tabulku hráčů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $competitionsArray = array();
        foreach ($competitions as $competition) {
            $competitionsArray[$competition->getId()] = $competition->getName();
        }
        return $competitionsArray;
    }

    protected function setDefaults($form) {
        if (!array_key_exists($this->playsTable->getClubHome()->getId(), $this->setClubsAssoc($this->clubsList->getClubsList()))) {
            $form['klub_domaci']->setPrompt('Zvolte domácí klub');
            $form['klub_hoste']->setPrompt('Zvolte hostující klub');
        } else {
            $form->setDefaults([
                'id_soutez' => $this->playsTable->getCompetition()->getId(),
                'rocnik' => $this->playsTable->getSeason(),
                'kolo' => $this->playsTable->getRound()->getNumber(),
                'klub_domaci' => $this->playsTable->getClubHome()->getId(),
                'klub_hoste' => $this->playsTable->getClubVisitors()->getId(),
                'utkani_kontumace_domaci' => $this->playsTable->getLossDefaultHome(),
                'utkani_kontumace_hoste' => $this->playsTable->getLossDefaultVisitors(),
                'utkani_datum' => is_null($this->playsTable->getDate()) ? null : date_format($this->playsTable->getDate(), "d.m.Y"),
                /* 'datum_nahradni' => is_null($this->playsTable->getDateAlternative()) ? null : date_format($this->playsTable->getDateAlternative(), "d.m.Y"), */
                'utkani_info' => $this->playsTable->getDescriptions()
            ]);
        }
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->playsTable->updatePlaysTable($id, $values);
            $this->playsTable->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            $this->presenter->flashMessage('Chyba při úpravě utkání. Utkání s danými parametry nelze takto upravit, zkontrolujte správnost všech údajů, tedy správnou příslušnost klubů k soutěži v kombinaci s ročníkem a systémem soutěží. Není již uvedené utkání vloženo? Změny vráceny zpět.', 'error');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Sport:utkaniUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            $this->mailer->setMessage('Chyba - úprava utkání', 'Nastala nespecifikovaná chyba při editaci utkání - úpravě. Jednalo se o utkání s id ' . $this->presenter->getParameter('id') . ' v ročníku ' . $form['rocnik']->getValue() . ' kole ' . $form['kolo']->getValue() . ' mezi kluby' . $form['klub_domaci']->getValue() . ' a ' . $form['klub_hoste']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při úpravě utkání. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Sport:utkaniUprava', $this->presenter->getParameter('id')); 
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();

            $this->playsTable->insertPlaysTable($values);
            $this->playsTable->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný hráč - ' . $form['id_hrac']->getSelectedItem() . ' - je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět');
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            //$this->presenter->flashMessage('Chyba při vkládání utkání. Utkání s danými parametry nelze vložit, zkontrolujte správnost všech údajů. Není již uvedené utkání jednou vloženo? Splňuje všechny náležitosti - herní systém soutěže dle počtu kol, apod? Změny vráceny zpět.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            $this->mailer->setMessage('Chyba - vkládání utkání', 'Nastala nespecifikovaná chyba při editaci utkání - vkládání. Jednalo se o utkání v ročníku ' . $form['rocnik']->getValue() . ' kole ' . $form['kolo']->getValue() . ' mezi kluby' . $form['klub_domaci']->getValue() . ' a ' . $form['klub_hoste']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při vkládání utkání. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');            
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
