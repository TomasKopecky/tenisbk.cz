<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\Memberships;
use App\Model\Entity\SportEntity\CompetitionsList;
use App\Model\Entity\SportEntity\ClubsList;
use App\Module\ErrorMailer;

// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání registrací { 
class MembershipForm extends BasicForms {

    private $membership,
            $competitionsList,
            $clubsList,
            $mailer;

    public function __construct(Memberships $membership, CompetitionsList $competitionsList, ClubsList $clubsList, ErrorMailer $mailer) {
        $this->membership = $membership;
        $this->competitionsList = $competitionsList;
        $this->clubsList = $clubsList;
        $this->mailer = $mailer;
    }

    public function start() {
        if (is_null($this->membership->getDateSince())) {
            $this->membership->setId($this->presenter->getParameter('id'));
            $this->membership->calcMembership();
        }
    }

    public function getOptionsList() {
        $this->competitionsList->calcCompetitionsList();
        $this->clubsList->calcClubsList();
    }

    public function create($operation, $presenter, $membership = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($membership)) {
            $this->membership = $membership;
        }
        $this->getOptionsList();
        $form = new Form();
        $form->addSelect('id_klub', 'Klub', $this->setClubsAssoc($this->clubsList->getClubsList()))
                ->setPrompt('Zvolte klub')
                ->setRequired('Je třeba vybrat klub pro registraci');
        $form->addSelect('id_soutez', 'Soutěž', $this->setCompetitionsAssoc($this->competitionsList->getCompetitionsList()))
                ->setPrompt('Zvolte soutěž')
                ->setRequired('Je třeba vybrat soutěž pro působení');
        $form->addText('automaticka_registrace')
                ->setHtmlType('number')
                ->setRequired(false)
                ->addRule(Form::RANGE, 'Lze zadat pouze ročník v rozmezí %d - %d', [2000, 2100]);
        $form->addText('datum_od', 'Datum od')
                ->setHtmlAttribute('readonly')
                ->setRequired('Zvolte datum od');
        $form->addText('datum_do', 'Datum do')
                ->setRequired(false)
                ->setHtmlAttribute('readonly');
        $form->addTextArea('pusobeni_info', 'Info o působení')
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(200);
        $form->addSubmit('membershipButton', 'Uložit');
        $form->onValidate[] = [$this, 'validateDate'];
        if ($this->operation == 'update') {
            $this->start();
            $this->setDefaults($form);
            $form->onValidate[] = [$this, 'validateForm'];
        }
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    public function validateForm($form) {
        if ($form['id_soutez']->getSelectedItem() == $this->membership->getCompetition()->getName() &&
                $form['id_klub']->getSelectedItem() == $this->membership->getClub()->getName() &&
                $form['datum_od']->getValue() == (is_null($this->membership->getDateSince()) ? null : date_format($this->membership->getDateSince(), "d.m.Y")) &&
                $form['datum_do']->getValue() == (is_null($this->membership->getDateUntil()) ? null : date_format($this->membership->getDateUntil(), "d.m.Y")) &&
                $form['pusobeni_info']->getValue() == $this->membership->getDescriptions()) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
    }

    public function validateDate($form) {
        if ($form['datum_do']->getValue() != null) {

            if (strtotime($form['datum_od']->getValue()) >= strtotime($form['datum_do']->getValue())) {
                $form->addError('Chyba - datum do musí být větší než datum od');
            }
        }
    }

    public function setCompetitionsAssoc($competitions) { // převede získanou tabulku hráčů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $competitionsArray = array();
        foreach ($competitions as $competition) {
            $competitionsArray[$competition->getId()] = $competition->getName();
        }

        return $competitionsArray;
    }

    public function setClubsAssoc($clubs) { // převede získanou tabulku klubů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $clubArray = array();
        foreach ($clubs as $club) {
            $clubArray[$club->getId()] = $club->getName();
        }

        return $clubArray;
    }

    protected function setDefaults($form) {
        $form->setDefaults([
            'id_soutez' => $this->membership->getCompetition()->getId(),
            'id_klub' => $this->membership->getClub()->getId(),
            'datum_od' => is_null($this->membership->getDateSince()) ? null : date_format($this->membership->getDateSince(), "d.m.Y"),
            'datum_do' => is_null($this->membership->getDateUntil()) ? null : date_format($this->membership->getDateUntil(), "d.m.Y"),
            'pusobeni_info' => $this->membership->getDescriptions()
        ]);
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->membership->updateMembership($id, $values);
            $this->membership->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            $this->presenter->flashMessage('Chyba při úpravě působeni. Zadaný klub byl je již ve zvoleném období v nějaké soutěi zaregistrován. Změny vráceny zpět.', 'error');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Sport:pusobeniUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->mailer->setMessage('Chyba - úprava členství v soutěži', 'Nastala nespecifikovaná chyba při editaci členství v soutěži - úpravě. Jednalo se o členství s id ' . $this->presenter->getParameter('id') . ', klubu ' . $form['id_klub']->getValue() . ', v soutěži ' . $form['id_soutez']->getValue() . ', v obdobi od ' . $form['datum_od']->getValue() . ' do ' . $form['datum_do']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();

            $this->presenter->flashMessage('Chyba při úpravě působeni. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Sport:pusobeniUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();
            $this->membership->insertMembership($values);
            $this->membership->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný klub - ' . $form['id_klub']->getSelectedItem() . ' - je již ve zvoleném období v nějaké soutěži zaregistrován. Změny vráceny zpět');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při vkládání působení. Zadaný klub - ' . $form['id_klub']->getSelectedItem() . ' - je již ve zvoleném období v nějaké soutěži zaregistrován. Změny vráceny zpět', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            $this->mailer->setMessage('Chyba - vkládání členství v soutěži', 'Nastala nespecifikovaná chyba při editaci členství v soutěži - vkldání. Jednalo se o členství klubu ' . $form['id_klub']->getValue() . ', v soutěži ' . $form['id_soutez']->getValue() . ', v obdobi od ' . $form['datum_od']->getValue() . ' do ' . $form['datum_do']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();

            $this->presenter->flashMessage('Chyba při vkládání působení. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
