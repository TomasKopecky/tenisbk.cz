<?php

//namespace App\Form\UserForm;

use Nette\Application\UI\Form;
use App\Model\Entity\UserEntity\Users;
use App\Module\AdminMailer;
use App\Module\ErrorMailer;

class SignUpForm
{    
    private $user;
    private $presenter;
    private $hash;
    private $adminMailer;
    private $errorMailer;
    
    public function __construct(Users $user, AdminMailer $adminMailer, ErrorMailer $errorMailer) {
        $this->user = $user;
        $this->adminMailer = $adminMailer;
        $this->errorMailer = $errorMailer;
    }
    
    public function start() {
        if (is_null($this->user)) {
            $this->user->setId($form['id_uzivatel']->getValue());
            $this->user->calcUser();
        }
    }
    
    public function create($presenter, $regUser) // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
    {
        $this->presenter = $presenter;
        $this->hash = $this->presenter->getParameter('hash');
        $form = new Form();
        $form->addHidden('id_uzivatel');
        $form->addText('jmeno')
                ->setAttribute('readonly');
        $form->addText('uzivatelske_jmeno')
                ->setHtmlAttribute('placeholder', 'Uživatelské jméno')
                ->setRequired('Zadejte uživatelské jméno')
                ->addRule(Form::PATTERN, 'Lze používat pouze písmena bez diakritiky a čísla', '[0-9a-zA-Z]*');
        $form->addText('email')
                ->setAttribute('readonly');
                //->setHtmlAttribute('placeholder', 'E-mailová adresa')
                //->setRequired('Zadejte e-mailovou adresu')
                //->addRule(Form::PATTERN, 'Zadejte e-mailovou adresu v platném tvaru', '^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}');
        $form->addPassword('heslo')
                ->setHtmlAttribute('placeholder', 'Heslo')
                ->setRequired('Zadejte heslo')
                ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8)
                ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jednu číslici a jedno velké písmeno', '(?=.*\d)(?=.*[A-Z]).*');
        $form->addPassword('reHeslo')
                ->setHtmlAttribute('placeholder', 'Heslo znovu')
                ->setRequired('Opakujte zadání hesla')
                ->addRule(Form::EQUAL, 'Zadali jste rozdílná hesla',$form['heslo']);
        $form->addSubmit('signUpButton', 'Registrovat');
        $form->onSuccess[] = array($this, 'signUp');
        $this->start();
        if (!is_null($regUser))
        {
                    $this->user = $regUser;
                $this->setDefaults($form);
        }
        return $form;
    }
    
    private function setDefaults($form)
    {
            $form->setDefaults([
            'id_uzivatel' => $this->user->getId(),
            'jmeno' => $this->user->getName(),
                'email' => $this->user->getEmail()
            ]);            
    }
    
        public function validateForm($form) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        
    }
    
    public function signUp($form) //provede se po odeslání vyplněného formuláře pro úpravu hráče
    {
        try {
            $values = $form->getValues();
            $this->user->registerUser($form['id_uzivatel']->getValue(), $values);         
                $this->adminMailer->setMessage('Registrace uživatele', 'Uživatel "' . $form['jmeno']->getValue() . '", pod jménem "' .  $form['uzivatelske_jmeno']->getValue() . '" s e-mailovou adresou "' . $form['email']->getValue() . ' se úspěšně zaregistroval', 'signUp');
                $this->adminMailer->setSenderReceiver("admin@tenisbk.cz", "admin@tenisbk.cz");
                $this->adminMailer->sendMessage();
            
        } catch (\Nette\Database\DriverException $e) {
            $this->presenter->flashMessage('Registrace neúspěšná - zadané uživatelské jméno je již použito jiným uživatelem, zvolte jiné', 'error');
            $this->presenter->redirect(':Web:SignUp:default', $this->presenter->getParameter('hash'));

        } catch (\Nette\Neon\Exception $e) {
            $this->errorMailer->setMessage('Chyba - registrace uživatele', 'Nastala nespecifikovaná chyba při registraci uživatele - vložení. Jednalo se o uživatele s id ' . $form['id_uzivatel']->getValue() . ' , jménem ' . $form['jmeno']->getValue() . ' a e-mailem ' . $form['email']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->errorMailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->errorMailer->sendMessage();
            
            $this->presenter->flashMessage('Registrace neúspěšná - nezjištěný typ chyby - kontaktujte správce', 'error');
            $this->presenter->redirect(':Web:SignUp:default', $this->presenter->getParameter('hash'));
        }       
    }
}