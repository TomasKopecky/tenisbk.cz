<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\UserEntity\Users;
use App\Module\ErrorMailer;
use App\Module\NoReplyMailer;

class UserForm extends BasicForms {

    private $user,
            $errorMailer,
            $noReplyMailer,
            $regMessageSent = false;

    public function __construct(Users $user, ErrorMailer $errorMailer, NoReplyMailer $noRemplyMailer) {
        $this->user = $user;
        $this->errorMailer = $errorMailer;
        $this->noReplyMailer = $noRemplyMailer;
    }

    public function start() {
        if (is_null($this->user->getName())) {
            $this->user->setId($this->presenter->getParameter('id'));
            $this->user->calcUser();
        }
    }

    public function create($operation, $presenter, $user = null) {
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($user)) {
            $this->user = $user;
        }
        $form = new Form();
        $form->addText('jmeno', 'Jméno')
                ->setRequired('Zadejte jméno uživatele')
                ->setMaxLength(50);
        $form->addText('email', 'E-mailová adresa')
                ->setRequired('Zadejte e-mailovou adresu uživatele')
                ->addRule(Form::EMAIL, 'Zadejte e-mailovou adresu ve správném tvaru')
                ->setMaxLength(50);
        $form->addCheckbox('aktivni', 'Uživatel aktivní');
        $form->addText('stav_registrace', 'Stav registrace')
                ->setAttribute('readonly');
        $form->addTextArea('uzivatel_info', 'Info o uživateli')
                ->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a číslice)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s]*$')
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(50);
        $form->addSubmit('userButton', 'Uložit')->onClick[] = array($this, 'validateForm');
        if ($this->operation == 'update') {
            $this->start();
            if (is_null($this->user->getRegHash())) {
                $form->addText('uzivatelske_jmeno', 'Uživatelské jméno')
                        ->setRequired(false)
                        ->setRequired('Zadejte uživatelské jméno uživatele')
                        ->setMaxLength(20);
                $form['stav_registrace']->setValue('Dokončena');
            } else {
                $form['stav_registrace']->setValue('Čeká');
                //$form->addText('registracni_hash', 'Registrační link')
                //        ->setAttribute('readonly');
                $form->addSubmit('vyzva_button','Zaslat výzvu na e-mail')->setValidationScope([])
                        ->onClick[] = array($this,'sendRegMessage');
            }
            $this->setDefaults($form);
            //$form->onValidate[] = [$this, 'validateForm'];
        } else {
            $form->addCheckbox('vyzva', 'Poslat výzvu na e-mail');
        }
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    public function validateForm($button) {
        $form = $button->getForm();
        if ($form['jmeno']->getValue() == $this->user->getName() &&
                (isset($form['uzivatelske_jmeno']) ? ($form['uzivatelske_jmeno']->getValue() == $this->user->getUsername()) : 1 == 1) &&
                $form['aktivni']->getValue() == $this->user->getActiveStatus() &&
                $form['email']->getValue() == $this->user->getEmail() &&
                $form['uzivatel_info']->getValue() == $this->user->getDescriptions()) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
    }

    protected function setDefaults($form) {
        $form->setDefaults([
            'jmeno' => $this->user->getName(),
            'uzivatelske_jmeno' => $this->user->getUsername(),
            'aktivni' => $this->user->getActiveStatus(),
            'email' => $this->user->getEmail(),
            //'registracni_hash' => $this->presenter->link(':Web:SignUp:default', $this->user->getRegHash()),
            'uzivatel_info' => $this->user->getDescriptions()
        ]);
    }

    public function sendRegMessage($button) {
        $form = $button->getForm();
        try {
            $this->regMessageSent = true;
            $this->noReplyMailer->setMessage('Potvrzení registrace', $this->user->getRegHash(), true);
            $this->noReplyMailer->setSenderReceiver('neodpovidat@tenisbk.cz', $form['email']->getValue());
            $this->noReplyMailer->sendMessage();
        } catch (\Nette\Mail\SendException $e) {
            $this->presenter->flashMessage('Chyba při odesílání výzvy e-mailem. Text chyby: ' . $e->getMessage(), 'error');
        }
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            if ($this->regMessageSent) {
                $this->presenter->flashMessage('Výzva k registraci zaslána na uvedený e-mail ' . $form['email']->getValue(), 'info');
            } else {
                $id = $this->presenter->getParameter('id');
                $values = $form->getValues();
                $this->user->updateUser($id, $values);
                $this->user->logUpdate();
            }
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            $this->presenter->flashMessage('Chyba při úpravě uživatele. Uživatel stejného jména, uživatelského jména a e-mailu nebo jejich kombinace se již v databázi nacházi. Změny vráceny zpět.', 'error');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Uzivatele:uzivateleUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            $this->errorMailer->setMessage('Chyba - úprava uživatele', 'Nastala nespecifikovaná chyba při editaci uživatele - úprava. Jednalo se o uživatele s id ' . $this->presenter->getParameter('id') . ', jménem ' . $form['jmeno']->getValue() . ' ,uživatelským jménem' . $form['uzivatelske_jmeno']->getValue() . ' a e-mailem ' . $form['email']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->errorMailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->errorMailer->sendMessage();
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při úpravě uživatele. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Uzivatele:uzivatelUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();
            $this->user->insertUser($values);
            $this->user->logInsert();
            if ($form['vyzva']->getValue()) {
                $this->sendRegMessage($form);
            }
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný hráč - ' . $form['id_hrac']->getSelectedItem() . ' - je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při vkládání uživatele. Uživatel stejného uživatelského jména, uživatelského jména a e-mailu nebo jejich kombinace se již v databázi nacházi.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            $this->errorMailer->setMessage('Chyba - vkládání uživatele', 'Nastala nespecifikovaná chyba při editaci uživatele - vkládání. Jednalo se o uživatele se jménem ' . $form['jmeno']->getValue() . ' ,uživatelským jménem' . $form['uzivatelske_jmeno']->getValue() . ' a e-mailem ' . $form['email']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->errorMailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->errorMailer->sendMessage();
//$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
