<?php

namespace App\WebModule\Presenters;

use Web\BasicPresenters\BasicPresenter;
use Nette\Application\UI;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of KontaktyPresenter
 *
 * @author TOM
 */
class KontaktyPresenter extends BasicPresenter{
    
    /**
     * Předání závislosti formou inject pro továrnu ContactForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \ContactForm @inject
     */
    public $contactFormFactory;
    
    public function renderDefault() {
        parent::renderDefault();
    }
    
    public function createComponentContactForm() {
        $form = $this->contactFormFactory->createComponentForm(); // metoda create z továrny ContactForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vaše zpráva byla úspěšně odeslána - brzy se ozveme na zadanou e-mailovou adresu ' . $form['email']->getValue(), 'info');
            $this->redirect('default');
        };
        return $form;
    }
}
