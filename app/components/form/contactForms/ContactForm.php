<?php

use Nette\Application\UI\Form;
use App\Module\ErrorMailer;
use App\Module\NoReplyMailer;
use App\Module\AdminMailer;

class ContactForm {

    private $errorMailer,
            $noreplyMailer,
            $adminMailer;

    public function __construct(ErrorMailer $errorMailer, NoReplyMailer $noReplyMailer, AdminMailer $adminMailer) {
        $this->errorMailer = $errorMailer;
        $this->noreplyMailer = $noReplyMailer;
        $this->adminMailer = $adminMailer;
    }

    public function createComponentForm() {
        $form = new Form();
        $form->addReCaptcha('captcha')
                ->setMessage('Potvrďte, že nejste robot')
                ->setRequired('Potvrďte, že nejste robot - zatrhněte volbu "Nejsem robot"');
        $form->addText('jmeno', 'Vaše jméno')
                ->setRequired('Zadejte vaše jméno');
        $form->addEmail('email', 'Váš e-mail')
                ->setRequired('Zadejte e-mail');
        $form->addText('predmet', 'Předmět')
                ->setRequired(false);
        $form->addTextArea('zprava', 'Text zprávy')
                ->setRequired('Zadejte text vaší zprávy')
                ->setMaxLength(1000);
        $form->onSuccess[] = array($this, 'sendMessage');
        return $form;
    }

    public function sendMessage($form) {
        try {
            $this->adminMailer->setMessage('Zpráva z kontaktního formuláře', 'Uživatel se zadaným jménem ' . $form['jmeno']->getValue() . ', e-mailem ' . $form['email']->getValue() . ', pod předmětem ' . $form['predmet']->getValue() . ' zaslal následující text: ' . $form['zprava']->getValue() . '. Zpráva byla odeslána ' . date('d.m.Y, H:i:s'), 'contact');
            $this->adminMailer->setSenderReceiver('admin@tenisbk.cz', 'admin@tenisbk.cz');
            $this->adminMailer->sendMessage();
            
            $this->noreplyMailer->setMessage('Potvrzení přijetí vaší zprávy - tenisbk.cz', 'Tímto potvrzujeme přijetí vaší zprávy v kontaktním formuláři na webu tenisbk.cz. V co nejkratší době se vám ozveme s odpovědí. Na tuto zprávu neodpovídejte.');
            $this->noreplyMailer->setSenderReceiver('neodpovidat@tenisbk.cz', $form['email']->getValue());
            $this->noreplyMailer->sendMessage();
        } catch (\Nette\Mail\SendException $e) {
            $form->addError(NULL);
            $this->errorMailer->setMessage('Chyba při odesílání zprávy (SendException)', 'Nastala nespecifikovaná chyba při odesílání zprávy v kontaktním formuláři. Jednalo se o uživatele, který zadal jméno ' . $form['jmeno']->getValue() . ' , e-mail ' . $form['email']->getValue() . ' a zprávu ' . $form['zprava']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->errorMailer->setSenderReceiver('error@tenisbk.cz', 'admin@tenisbk.cz');
            $this->errorMailer->sendMessage();
            $this->presenter->flashMessage('Chyba při odesílání zprávy. Nespecifikovaný typ, kontaktujte správce.', 'error');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
            $this->errorMailer->setMessage('Chyba při odesílání zprávy (obecná Exception)', 'Nastala nespecifikovaná chyba při odesílání zprávy v kontaktním formuláři. Jednalo se o uživatele, který zadal jméno ' . $form['jmeno']->getValue() . ' , e-mail ' . $form['email']->getValue() . ' a zprávu ' . $form['zprava']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->errorMailer->setSenderReceiver('error@tenisbk.cz', 'admin@tenisbk.cz');
            $this->errorMailer->sendMessage();
            $this->presenter->flashMessage('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.', 'error');
        }
    }

}
