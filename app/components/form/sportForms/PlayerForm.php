<?php

//namespace App\Form\SportForm;

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\Players;
use App\Module\ErrorMailer;

class PlayerForm extends BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání hráčů { { {
{
    private $player,
            $mailer;
    
    public function __construct(Players $player, ErrorMailer $mailer) {
        $this->player = $player;
        $this->mailer = $mailer;
    }

    protected function start() {
        if (is_null($this->player->getName())) {
            $this->player->setId($this->presenter->getParameter('id'));
            $this->player->calcPlayer();
        }
    }

    public function create($operation, $presenter, $player = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($player)) {
            $this->player = $player;
        }
        $form = new Form();
        $form->addText('jmeno', 'Jméno')
                ->setRequired('Zadejte jméno hráče')
                ->setMaxLength(50)
                ->addRule(Form::PATTERN, 'Zadejte jméno a příjmení hráče (pouze dvouslovná jména, jako oddělovač použijte mezeru), případně na konci frázi " ml." nebo " st."', '[A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ]{1}[a-z ěščřďťňžýáíéúů]+[ ]{1}[A-Z ĚŠČŘŤĎŇŽÝÁÍÉÚŮ]{1}[a-zA-Z-ěščřďťňžýáíéúůĚŠČŘŤĎŇŽÝÁÍÉÚŮ]+( ml.| st.)?$');
        $form->addRadioList('pohlavi', 'Pohlaví', array_flip($this->player::PLAYER_SEX))
                ->setRequired('Zvolte pohlaví hráče');
        $form->addRadioList('ruka', 'Silná ruka', array_flip($this->player::HAND))
                ->setRequired('Zvolte silnou ruku hráče');
        /*  OLD STYLE WITH BIRTHDATE NUMBER
        $form->addText('rodne_cislo', 'Rodné číslo')
                ->setRequired(false)
                ->setMaxLength(10)
                ->addRule(Form::PATTERN, 'Zadejte rodné číslo v platném tvaru (9-10 číslic bez lomítka)', '[0-9]{9,10}|^$');
         * 
         */
        $form->addInteger('rok_narozeni', 'Rok narození')
                ->setRequired(false)
                ->addRule($form::RANGE, 'Zadejte rok narození hráče v požadovaném tvaru (%d - %d)', [1920, date("Y")-5]);
                //->addRule(Form::PATTERN, 'Zadejte rok narození hráče v požadovaném tvaru (čtyřmístný rok)', '[0-9]{4}|^$');
        $form->addText('vyska', 'Výška')
                ->setHtmlAttribute('data-slider-value', 0)
                ->setRequired(false);
        $form->addText('vaha', 'Váha')
                ->setHtmlAttribute('data-slider-value', 0)
                ->setRequired(false);
        $form->addTextArea('hrac_info', 'Hráč info')
                ->setRequired(false)
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setAttribute('rows', 3)
                ->setMaxLength(200);
        $form->addSubmit('playerButton', 'Uložit');
        if ($this->operation == 'update') {
                    $this->start();
            $this->setDefaults($form);
            $form->onValidate[] = [$this, 'validateForm'];
        }
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    public function validateForm($form) {
        if ($form['jmeno']->getValue() == $this->player->getName() &&
                $form['pohlavi']->getValue() == $this->player->getSex() &&
                $form['ruka']->getValue() == $this->player->getHand() &&
                /* OLD SOLUTION WITH THE BIRTHNUMBER
                 * $form['rodne_cislo']->getValue() == $this->player->getBirthNumber() &&
                 */
                $form['rok_narozeni']->getValue() == $this->player->getBirthYear() &&
                $form['vyska']->getValue() == $this->player->getHeight() &&
                $form['vaha']->getValue() == $this->player->getWeight() &&
                $form['hrac_info']->getValue() == $this->player->getDescriptions()) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
    }

    protected function setDefaults($form) {
        $form->setValues([
            'jmeno' => $this->player->getName(),
            /*  OLD SOLUTION WITH THE BIRTHNUMBER
             * 'rodne_cislo' => $this->player->getBirthNumber(),
             */
            'rok_narozeni' => $this->player->getBirthYear(),
            'hrac_info' => $this->player->getDescriptions(),
            'pohlavi' => $this->player->getSex(),
            'ruka' => $this->player->getHand()]);

        $form['vyska']->setHtmlAttribute('data-slider-value', $this->player->getHeight());
        $form['vaha']->setHtmlAttribute('data-slider-value', $this->player->getWeight());
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->player->updatePlayer($id, $values);
            $this->player->logUpdate();
//$this->postgre_tennis_db->updatePlayer($this->id, $final_values);  // odkaz do modelu na provedení update v databázi
        } catch (\Nette\Database\DriverException $e) {
            //$this->presenter->flashMessage('Chyba při úpravě hráče. Hráč stejného jména, roku narození nebo jejich kombinace se již v databázi nacházi. Změny vráceny zpět.', 'error');
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect(':Admin:Sport:hraciUprava', $this->presenter->getParameter('id'));
            //$this->presenter->flashMessage('Chyba při úpravě hráče. Hráč stejného jména, rodného čísla nebo jejich kombinace se již v databázi nacházi. Změny vráceny zpět.', 'error');
            //$this->presenter->redirect('Sport:hraciUprava', $this->id);
        } catch (\Nette\Neon\Exception $e) {
            $this->mailer->setMessage('Chyba - úprava hráče', 'Nastala nespecifikovaná chyba při editaci hráče - úpravě. Jednalo se o hráče s id ' . $this->presenter->getParameter('id') . ', jménem ' . $form['jmeno']->getValue() . ', rokem narození ' . $form['rok_narozeni']->getValue() . ' pohlavím ' . $form['pohlavi']->getValue() . ' a rukou' . $form['ruka']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při úpravě hráče. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect(':Admin:Sport:hraciUprava', $this->presenter->getParameter('id'));
            //$this->presenter->flashMessage('Chyba při úpravě hráče. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:hraciUprava', $this->id);
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();
            $this->player->insertPlayer($values);
            $this->player->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            //$this->presenter->flashMessage('Chyba při vkládání hráče. Hráč stejného jména, roku narození nebo jejich kombinace se již v databázi nacházi.', 'error');
            //$this->presenter->redirect('Sport:hraciNovy');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
           
            $this->mailer->setMessage('Chyba - vkládání hráče', 'Nastala nespecifikovaná chyba při editaci hráče - vkládání. Jednalo se o hráče se jménem ' . $form['jmeno']->getValue() . ', rokem narození ' . $form['rok_narozeni']->getValue() . ' pohlavím ' . $form['pohlavi']->getValue() . ' a rukou' . $form['ruka']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
           
            $this->presenter->flashMessage('Chyba při vkládání hráče. Nespecifikovaný typ, kontaktujte správce', 'error');
            //$this->presenter->redirect(':Admin:Sport:hraci');
//$form->addError('Chyba při vkládání hráče. Nespecifikovaný typ, kontaktujte správce.');
//$this->presenter->flashMessage('Chyba při vkládání hráče. Nespecifikovaný typ, kontaktujte správce.', 'error');
//$this->presenter->redirect('Sport:hraciNovy');
        }
    }

}
