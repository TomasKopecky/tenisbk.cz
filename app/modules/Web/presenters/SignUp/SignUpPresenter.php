<?php

namespace App\WebModule\Presenters;
use Nette\Application\UI;
use App\BasicLayoutPresenters\BasicPresenterLayout;
use App\Model\Entity\UserEntity\UsersList;

class SignUpPresenter extends BasicPresenterLayout
{
    /**
     * Předání závislosti formou inject pro továrnu SignUpForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \SignUpForm @inject
     */
    public $signUpFormFactory;
    private $usersList,
            $regUser;
    
    public function __construct(UsersList $usersList) {
        parent::__construct();
        $this->usersList = $usersList;
    }
    
    public function startup() {
        parent::startup();
        $this->checkLogin();
    }

    public function renderDefault($hash)
    { 
        $this->usersList->calcUsersList();
        $users = $this->usersList->getUsersList();
        foreach ($users as $user)
        {
            if ($user->getRegHash() == $hash)
            {
                $this->regUser = $user;
            }
        }
        if ($this->regUser == NULL) {
            $this->flashMessage('Registrace se zadaným kódem neexistuje','error');
            $this->redirect(':Web:Homepage:default');
        }  
    }
    
    public function checkLogin() {
        $user = $this->getUser();
        
        // pokud stránku navštěvuje příhlášený uživatel, odmítni a přesměruj zpět na homepage
        if (($user->isLoggedIn())) {
            $this->flashMessage('Pro přístup do sekce registrace uživatelů nesmíte být přihlášen', 'error');            
            $this->redirect(':Web:Homepage:default');
        }      
    }
    
    public function createComponentLoginForm()
    {
        $form = $this->loginFormFactory->create($this);
        
        $form->onSuccess[] = function (UI\Form $form) {
            $this->redirect(':Web:Homepage:default');
        };
        
        return $form;
    }

    public function createComponentSignUpForm()
    {
        $form = $this->signUpFormFactory->create($this, $this->regUser); // metoda create z továrny SignUpForm
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Registrace uživatele "' . $form['uzivatelske_jmeno']->getValue() . '" úspěšně provedena, můžete se přihlásit','info');
            $this->redirect(':Web:Homepage:default');
        };
        return $form;
    }
}