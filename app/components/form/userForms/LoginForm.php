<?php

//namespace App\Form\UserForm;

use Nette\Application\UI\Form;
use App\Model\Entity\UserEntity\LogsList;
use App\Model\Entity\UserEntity\Events;
use App\Model\Entity\UserEntity\Users;
use App\Module\ErrorMailer;

class LoginForm {

    private $presenter;
    private $log,
            $event,
            $users,
            $mailer;

    public function __construct(LogsList $log, Events $event, Users $users, ErrorMailer $mailer) {
        $this->log = $log;
        $this->event = $event;
        $this->users = $users;
        $this->mailer = $mailer;
    }

    public function create($presenter) {
        $this->presenter = $presenter;
        $form = new Form();
        $form->addText('uzivatelske_jmeno', 'Přihlašovací jméno')
                ->setRequired('Zadejte uživatelské jméno či e-mail')
                ->setHtmlAttribute('placeholder', 'Přihlašovací jméno či e-mail');
        $form->addPassword('heslo', 'Heslo')
                ->setRequired('Zadejte heslo')
                ->setHtmlAttribute('placeholder', 'Heslo');
        $form->addSubmit('loginButton', 'Odeslat');
        $form->onSuccess[] = array($this, 'submitted');
        return $form;
    }

    public function submitted($form, $values) {
        $user = $this->presenter->getUser();
        try {
            $user->login($values->uzivatelske_jmeno, $values->heslo);
            $user->setExpiration('30 minutes');
            $this->users->setId($user->getId());
            $this->log->setSuccessfulLoginLog($this->users);
            $this->presenter->redirect('default');
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->presenter->flashMessage('Zadány neplatné přihlašovací údaje', 'error');
            $this->presenter->redirect('default');
        } catch (Nette\Neon\Exception $e) {
            $this->mailer->setMessage('Chyba - přihlašování do administračního režimu', 'Nastala nespecifikovaná chyba při pokusu o přihlášení do administračního režimu. Jednalo se o uživatele s username ' . $values->uzivatelske_jmeno);
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Nezjištěná chyba, kontaktujte admina', 'error');
            $this->presenter->redirect('default');
        }
    }

    public function logout($presenter) {
        $user = $presenter->getUser();
        try {
            $user->logout();
            $this->users->setId($user->getId());
            $this->log->setSuccessfulLogoutLog($this->users);
            //$this->flashMessage('Uživatel '. $user->id . ' úspěšně odhlášen', 'warning');
            if ($presenter->getName() == 'Admin:Sport' || $presenter->getName() == 'Admin:Uzivatele' || $presenter->getName() == 'Admin:Cms') {
                $presenter->redirect(':Web:Homepage:default');
            } else {
                $presenter->redirect('default');
            }
        } catch (Nette\Neon\Exception $ex) {
            $this->mailer->setMessage('Chyba - odhlašování z administračního režimu', 'Nastala nespecifikovaná chyba při pokusu o odhlášení z administračního režimu. Jednalo se o uživatele s id ' . $user->getId());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při odhlášení - kontaktujte administrátora', 'error');
            $this->presenter->redirect('default');
        }
    }

}
