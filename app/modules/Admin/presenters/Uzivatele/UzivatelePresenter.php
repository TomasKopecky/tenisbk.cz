<?php

/**
 * Třída editačního presenteru uživatelů modulu Admin - přístupná pouze administrátorům
 * Správa uživatelské databáze
 *
 * @category Admin_Module
 * @subcat
 * @package  Tennis_Competitions_Blansko
 * @author   Tomáš Kopecký <kopeck32@student.vspj.cz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://www.tenisbk.cz
 */

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use App\BasicLayoutPresenters\BasicPresenterLayout;
use App\Model\Entity\UserEntity\UsersList;
use App\Model\Entity\UserEntity\RoleLogsList;
use App\Model\Entity\UserEntity\LogsList;
use App\Model\Entity\UserEntity\RolesList;
use App\Module\ErrorMailer;

class UzivatelePresenter extends BasicPresenterLayout {

    /**
     * Předání závislosti formou inject pro továrnu PlayerForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \RoleLogForm @inject
     */
    public $roleLogFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu PlayerForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \UserForm @inject
     */
    public $userFormFactory;
    private $roleLogsList,
            $usersList,
            $logsList,
            $rolesList,
            $mailer;

    //private $mailer;

    public function __construct(RoleLogsList $roleLogsList, UsersList $usersList, LogsList $logsList, RolesList $rolesList, ErrorMailer $mailer) {
        parent::__construct();
        $this->roleLogsList = $roleLogsList;
        $this->usersList = $usersList;
        $this->logsList = $logsList;
        $this->rolesList = $rolesList;
        $this->mailer = $mailer;
    }

    /**
     * Metoda pro zjištění identity přihlášeného uživatele a případné přesměrování
     *
     * @return void
     */
    public function startup() {
        parent::startup();
        $this->checkLogin();
        $this->checkRoles(array('Admin'));
    }

    /*
     * Výpis všech aktivních uživatelů v databázi
     *
     * @return void
     */

    public function renderOpravneni() {
        $this->roleLogsList->calcRoleLogsList();
        $this->template->roleLogsList = $this->roleLogsList->getRoleLogsList();
    }

    public function renderUzivatele() {
        $this->usersList->calcUsersList();
        $this->template->usersList = $this->usersList->getUsersList();
    }

    public function renderOpravneniNove() {
        $this->template->formName = "insertRoleLogForm";
    }

    public function renderUzivateleNovy() {
        $this->template->formName = "insertUserForm";
    }

    public function renderUzivateleUprava($id) {
        if (is_null($this->usersList->getName())) {
            $this->usersList->setId($id);
            $this->usersList->calcUser();
        }
        if (empty($this->usersList->getName())) {
            $this->presenter->flashMessage('Uživatel zadaného ID není v databázi', 'error');
            $this->presenter->redirect('Uzivatele:uzivatele');
        }
        $this->template->formName = "updateUserForm";
    }

    public function renderOpravneniUprava($id) {
        if (is_null($this->roleLogsList->getUser())) {
            $this->roleLogsList->setId($id);
            $this->roleLogsList->calcRoleLog();
        }
        if (empty($this->roleLogsList->getUser()->getName())) {
            $this->presenter->flashMessage('Oprávnění zadaného ID není v databázi', 'error');
            $this->presenter->redirect('Uzivatele:opravneni');
        }
        $this->template->formName = "updateRoleLogForm";
    }

    public function renderLogy() {
        $this->logsList->calcLogsList();
        $this->template->logsList = $this->logsList->getLogsList();
    }

    public function createComponentUpdateRoleLogForm() {
        $form = $this->roleLogFormFactory->create('update', $this, $this->roleLogsList); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava práv uživatele \'' . $form['id_uzivatel']->getSelectedItem() . '\' s oprávněním \'' . $form['id_role']->getSelectedItem() . '\' provedena', 'info');
            $this->redirect('Uzivatele:opravneni');
        };
        return $form;
    }

    public function createComponentInsertRoleLogForm() {
        $form = $this->roleLogFormFactory->create('insert', $this); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení práv uživatele \'' . $form['id_uzivatel']->getSelectedItem() . '\' s oprávněním \'' . $form['id_role']->getSelectedItem() . '\' provedena', 'info');
            $this->redirect('Uzivatele:opravneni');
        };
        return $form;
    }

    public function createComponentUpdateUserForm() {
        $form = $this->userFormFactory->create('update', $this, $this->usersList); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava uživatele \'' . $form['jmeno']->getValue() . '\' provedena', 'info');
            $this->redirect('Uzivatele:uzivatele');
        };
        return $form;
    }

    public function createComponentInsertUserForm() {
        $form = $this->userFormFactory->create('insert', $this); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení uživatele \'' . $form['jmeno']->getValue() . '\' provedeno', 'info');
            if ($form['vyzva']->getValue() == true) {
                $this->flashMessage('Zaslání výzvy na \'' . $form['email']->getValue() . '\' provedeno', 'info');
            }
            $this->redirect('Uzivatele:uzivatele');
        };
        return $form;
    }

    public function handleDeleteUser($id, $name) {
        if (is_null($this->usersList->getId())) {
            $this->usersList->setId($id);
            $this->usersList->setName($name);
        }

        try {
            $this->usersList->deleteUser();
            $this->usersList->logDelete();
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání uživatele ' . $this->usersList->getName() . '. Uživatel má již přiděleno oprávnění nebo má již historii v logu. Uživatele tedy pouze deaktivujte.', 'error');

            $this->redirect('uzivatele');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání uřivatele', 'Nastala nespecifikovaná chyba při editaci uživatele - mazání. Jednalo se o uživatele s id ' . $this->usersList->getId() . ', a jménem ' . $this->usersList->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();

            $this->flashMessage('Nezjištěná chyba při mazání uživatele ' . $this->usersList->getName() . ' - kontaktuje administrátora', 'error');
            $this->redirect('uzivatele');
        }
        $this->flashMessage('Vymazání uživatele ' . $this->usersList->getName() . ' úspěšně provedeno', 'info');
        $this->redirect('uzivatele');
    }

    public function handleDeleteRoleLog($id, $name, $roleName) {
        if (is_null($this->roleLogsList->getId())) {
            $this->roleLogsList->setId($id);
            $user = $this->usersList;
            $user->setName($name);
            $this->roleLogsList->setUser($user);
            $role = $this->rolesList;
            $role->setTitle($roleName);
            $this->roleLogsList->setRole($role);
        }

        try {
            $this->roleLogsList->deleteRoleLog();
            $this->roleLogsList->logDelete();
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Databázová chyba při mazání oprávnění ' . $this->roleLogsList->getRole()->getTitle() . ' uživatele ' . $this->roleLogsList->getUser()->getName() . '. - kontaktujte administrátora.', 'error');

            $this->redirect('opravneni');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání oprávnění', 'Nastala nespecifikovaná chyba při editaci oprávnění - mazání. Jednalo se o oprávnění s id ' . $this->roleLogsList->getId() . ', s názvem ' . $this->roleLogsList->getRole()->getTitle() . ', uživatele ' . $this->roleLogsList->getUser()->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();

            $this->flashMessage('Nezjištěná chyba při mazání oprávnění ' . $this->roleLogsList->getRole()->getTitle() . ' uživatele ' . $this->roleLogsList->getUser()->getName() . '. - kontaktujte administrátora.', 'error');
            $this->redirect('opravneni');
        }
        $this->flashMessage('Vymazání oprávnění ' . $this->roleLogsList->getRole()->getTitle() . ' uživatele ' . $this->roleLogsList->getUser()->getName() . ' úspěšně provedeno', 'info');
        $this->redirect('opravneni');
    }

}
