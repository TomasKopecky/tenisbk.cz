<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\Registrations;
use App\Model\Entity\SportEntity\PlayersList;
use App\Model\Entity\SportEntity\ClubsList;
use App\Module\ErrorMailer;

class RegForm extends BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání registrací {
{
    private $registration,
            $playersList,
            $clubsList,
            $mailer;

    public function __construct(Registrations $registration, PlayersList $playersList, ClubsList $clubsList, ErrorMailer $mailer) {
        $this->registration = $registration;
        $this->playersList = $playersList;
        $this->clubsList = $clubsList;
        $this->mailer = $mailer;
    }

    public function start() {
        if (is_null($this->registration->getDateSince())) {
            $this->registration->setId($this->presenter->getParameter('id'));
            $this->registration->calcRegistration();
        }
    }
    
    public function getOptionsList()
    {
        $this->playersList->calcPlayersList();
        $this->clubsList->calcClubsList();
    }

    public function create($operation, $presenter, $registration = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($registration)) {
            $this->registration = $registration;
        }
        $this->getOptionsList();
        $form = new Form();
        $form->addSelect('id_hrac', 'Hráč', $this->setPlayersAssoc($this->playersList->getPlayersList()))
                ->setPrompt('Zvolte hráče')
                ->setRequired('Je třeba vybrat registrovaného hráče');
        $form->addSelect('id_klub', 'Klub', $this->setClubsAssoc($this->clubsList->getClubsList()))
                ->setPrompt('Zvolte klub')
                ->setRequired('Je třeba vybrat klub pro registraci');
        $form->addText('automaticka_registrace','Automatická registrace na celou sezonu - zvolte sezonu')
                ->setHtmlType('number')
                ->setRequired(false)
                ->addRule(Form::RANGE, 'Lze zadat pouze ročník v rozmezí %d - %d', [2000, 2100]);
        $form->addText('datum_od', 'Datum od')
                ->setHtmlAttribute('readonly')
                ->setRequired('Zvolte datum od');
        $form->addText('datum_do', 'Datum do')
                ->setRequired(false)
                ->setHtmlAttribute('readonly');
        $form->addText('hrac_muzi_poradi','Pořadí mužského hráče v klubu')
                ->setHtmlType('number')
                ->setRequired('Zadejte pořadí mužského hráče v klubu')
                ->addRule(Form::RANGE, 'Lze zadat pouze čísla v rozmezí 1 - 20', [1,30]);
        $form->addTextArea('registrace_info', 'Info o registraci')
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(200);
        $form->addSubmit('regButton', 'Uložit');
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
        $order = $form['hrac_muzi_poradi']->getValue() == 30 ? NULL : $form['hrac_muzi_poradi']->getValue();
        if (
                str_replace(' - Z','',str_replace(' - M','',$form['id_hrac']->getSelectedItem())) == $this->registration->getPlayer()->getName() &&
                $form['id_klub']->getSelectedItem() == $this->registration->getClub()->getName() &&
                $form['datum_od']->getValue() == (is_null($this->registration->getDateSince()) ? null : date_format($this->registration->getDateSince(), "d.m.Y")) &&
                $form['datum_do']->getValue() == (is_null($this->registration->getDateUntil()) ? null : date_format($this->registration->getDateUntil(), "d.m.Y")) &&
                $order == $this->registration->getOrder() &&
                //$form['hrac_muzi_poradi']->getValue() == $this->registration->getOrder() &&
                $form['registrace_info']->getValue() == $this->registration->getDescriptions()) {
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

    public function setPlayersAssoc($players) { // převede získanou tabulku hráčů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $playersArray = array();
        foreach ($players as $player) {
            $playersArray[$player->getId()] = $player->getName() . ' - ' . $player->getBirthYear() . ' - ' . $player->getSex();
        }

        return $playersArray;
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
            'id_hrac' => $this->registration->getPlayer()->getId(),
            'id_klub' => $this->registration->getClub()->getId(),
            'datum_od' => is_null($this->registration->getDateSince()) ? null : date_format($this->registration->getDateSince(), "d.m.Y"),
            'datum_do' => is_null($this->registration->getDateUntil()) ? null : date_format($this->registration->getDateUntil(), "d.m.Y"),
            'hrac_muzi_poradi' => $this->registration->getOrder(),
            'registrace_info' => $this->registration->getDescriptions()
        ]);
    }

    private function handlePlayerOrder($form){
            if ($form['hrac_muzi_poradi']->getValue() == 30){
                unset($form['hrac_muzi_poradi']);
            }
    }
    
    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $this->handlePlayerOrder($form);
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->registration->updateRegistration($id, $values);
            $this->registration->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            //$this->presenter->flashMessage('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.', 'error');
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Sport:registraceUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            $this->mailer->setMessage('Chyba - úprava registrace hráče v klubu', 'Nastala nespecifikovaná chyba při editaci registrace hráče v klubu - úpravě. Jednalo se o registraci s id ' . $this->presenter->getParameter('id') . ', hráče ' . $form['id_hrac']->getValue() . ', v klubu ' . $form['id_klub']->getValue() . ', v obdobi od ' . $form['datum_od']->getValue() . ' do ' . $form['datum_do']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Sport:registraceUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $this->handlePlayerOrder($form);
            $values = $form->getValues();
            $this->registration->insertRegistration($values);
            $this->registration->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný hráč - ' . $form['id_hrac']->getSelectedItem() . ' - je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět');
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            //$this->presenter->flashMessage('Chyba při vkládání působení. Zadaný hráč - ' . $form['id_hrac']->getSelectedItem() . ' - je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);            
            $this->mailer->setMessage('Chyba - vkládání registrace hráče v klubu', 'Nastala nespecifikovaná chyba při editaci registrace hráče v klubu - vkládání. Jednalo se o hráče ' . $form['id_hrac']->getValue() . ', v klubu ' . $form['id_klub']->getValue() . ', v obdobi od ' . $form['datum_od']->getValue() . ' do ' . $form['datum_do']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
//$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
