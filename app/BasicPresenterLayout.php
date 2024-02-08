<?php

// úvodní presenter, ze kterého dědí ty třídy presenteru, ve kterých není třeba předávat proměnné z BasicPresenteru - tedy např. minitabulky či formulář pro přihlášení

namespace App\BasicLayoutPresenters;

use Nette;

class BasicPresenterLayout extends Nette\Application\UI\Presenter {

    /** @var \LoginForm @inject */ // předání závislosti formou inject pro továrnu LoginForm z config souboru - zajistí automaticky vytvoření instance třídy
    public $loginFormFactory;

    //protected $user;

    /** formatLayoutTemplateFiles
     *
     * Vrací příslušné view layout šablony k presenterům dle nové adresářové struktury
     *
     * @return array
     */
    public function formatLayoutTemplateFiles() {
        $name = $this->getName();
        $layout = $this->layout ? $this->layout : 'layout';
        $dirName = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dirName/templates") ? $dirName : dirname($dirName);
        $list = array(
            "$dir/templates/@$layout.latte",
            "$dir/templates/@$layout.phtml",
        );
        do {
            $dir = dirname($dir);
            $list[] = "$dir/templates/@$layout.latte";
            $list[] = "$dir/templates/@$layout.phtml";
        } while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
        return $list;
    }

    /** formatTemplateFiles
     *
     * Vrací příslušné view šablony k presenterům dle nové adresářové struktury
     *
     * @return array
     */
    public function formatTemplateFiles() {
        $dirName = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dirName/templates") ? $dirName : dirname($dirName);
        return array(
            "$dir/templates/$this->view.latte",
            "$dir/templates/$this->view.phtml",
        );
    }

    public function handleLogout() { // handle (signál) pro odhlášení přihlášeného uživatele
        $this->loginFormFactory->logout($this);
    }

    /** checkLogin
     *
     * V případě, že přistupující uživatel není přihlášen, je přesměrován na homepage
     *
     * @return void
     */
    protected function checkLogin() {
        if (!($this->getUser()->isLoggedIn())) {
            $this->flashMessage('Pro přístup do dané sekce musíte být přihlášeni', 'error');
            $this->redirect(':Web:Homepage:');
        }
    }

    protected function checkRoles($roles = array()) {
        $accessValid = false;
        foreach ($roles as $role) {
            if (($this->getUser()->isInRole($role))) {
                $accessValid = true;
            }
        }
        if (!$accessValid) {
            $this->flashMessage('Pro přístup do dané sekce nemáte dostatečná oprávnění', 'error');
            $this->redirect(':Admin:Sport:default');
        }
    }

    protected function getCurrentYear() {
        return date("Y");
    }

    protected function getCurrentDate() {
        return date("Y-m-d");
    }

    protected function getCurrentMonth() {
        return date('m');
    }

    protected function getDatabaseErrorText($message) { // metoda pro vrácení zájmové části textu exception z databáze
        return substr($message, strpos($message, 'ERROR') + 6, strpos($message, 'CONTEXT') - (strpos($message, 'ERROR') + 6));
    }

}
