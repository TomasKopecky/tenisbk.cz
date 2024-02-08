<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\UserEntity\RoleLogs;
use App\Model\Entity\UserEntity\UsersList;
use App\Model\Entity\UserEntity\RolesList;
use App\Module\ErrorMailer;

class RoleLogForm extends BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání registrací {
{
    private $roleLog,
            $usersList,
            $rolesList,
            $activeUsers = array(),
            $mailer;

    public function __construct(RoleLogs $roleLog, RolesList $rolesList, UsersList $usersList, ErrorMailer $mailer) {
        $this->roleLog = $roleLog;
        $this->rolesList = $rolesList;
        $this->usersList = $usersList;
        $this->mailer = $mailer;
    }

    public function start() {
        if (is_null($this->roleLog->getDateSince())) {
            $this->roleLog->setId($this->presenter->getParameter('id'));
            $this->roleLog->calcRoleLog();
        }
    }
    
    public function getOptionsList()
    {
        $this->rolesList->calcRolesList();
        $this->usersList->calcUsersList();
    }

    public function create($operation, $presenter, $roleLog = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($roleLog)) {
            $this->roleLog = $roleLog;
        }
        $this->getOptionsList();
        $form = new Form();
        $form->addSelect('id_uzivatel', 'Uživatelske jméno',$this->setUsersAssoc($this->usersList->getUsersList()))
                                                ->setPrompt('Zvolte uživatele')
                                ->setRequired('Je třeba vybrat uživatele');
        $form->addSelect('id_role', 'Role', $this->setRolesAssoc($this->rolesList->getRolesList()))
                ->setPrompt('Zvolte roli')
                ->setRequired('Je třeba vybrat roli oprávnění');
        $form->addTextArea('opravneni_info', 'Info o oprávnění')
                ->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a číslice)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s]*$')                
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(50);
        $form->addSubmit('userButton', 'Uložit');
        if ($this->operation == 'update') {
                    $this->start();
            $this->setDefaults($form);
            $form->onValidate[] = [$this, 'validateForm'];
        }
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    public function validateForm($form) {
        if ($form['id_uzivatel']->getValue() == $this->roleLog->getUser()->getId() &&
                $form['id_role']->getValue() == $this->roleLog->getRole()->getId() &&
                $form['opravneni_info']->getValue() == $this->roleLog->getDescriptions()) {
                        $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
    }

    public function setUsersAssoc($users) { // převede získanou tabulku hráčů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $usersArray = array();
        foreach ($users as $user) {
            $usersArray[$user->getId()] = $user->getName();
        }

        return $usersArray;
    }
    
    public function setRolesAssoc($roles) { // převede získanou tabulku hráčů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $rolesArray = array();
        foreach ($roles as $role) {
            $rolesArray[$role->getId()] = $role->getTitle();
        }

        return $rolesArray;
    }

    protected function setDefaults($form) {
        $form->setDefaults([
            'id_uzivatel' => $this->roleLog->getUser()->getId(),
            'id_role' => $this->roleLog->getRole()->getId(),
            'opravneni_info' => $this->roleLog->getDescriptions()
        ]);
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $id = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->roleLog->updateRoleLog($id, $values);
            $this->roleLog->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            $this->presenter->flashMessage('Chyba při úpravě oprávnění. Daný uživatel již má přidělenu nějakou roli. Změny vráceny zpět.', 'error');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Uzivatele:opravneniUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            $this->mailer->setMessage('Chyba - úprava oprávnění uživateli', 'Nastala nespecifikovaná chyba při editaci oprávnění uživateli - úprava. Jednalo se o oprávnění s id ' . $this->presenter->getParameter('id') . ', uživatele s id ' . $form['id_uzivatel']->getValue() . ' a id role' . $form['id_role']->getValue() . 'Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při úpravě oprávnění. Daný uživatel již má přidělenu nějakou roli.', 'error');
            $this->presenter->redirect('Uzivatele:opravneniUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            $values = $form->getValues();
            $this->roleLog->insertRoleLog($values);
            $this->roleLog->logInsert();
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný hráč - ' . $form['id_hrac']->getSelectedItem() . ' - je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět');
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->flashMessage('Chyba při vkládání oprávnění. Daný uživatel již má přidělenu nějakou roli. Změny vráceny zpět', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            $this->mailer->setMessage('Chyba - vkládání oprávnění k uživateli', 'Nastala nespecifikovaná chyba při editaci oprávnění uživatele - vkládání. Jednalo se o uživatele s id ' . $form['id_uzivatel']->getValue() . ' a id role' . $form['id_role']->getValue() . 'Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
//$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->presenter->flashMessage('Chyba při vkládání oprávnění. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
