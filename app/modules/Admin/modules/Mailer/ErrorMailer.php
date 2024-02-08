<?php

namespace App\Module;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorMailer
 *
 * @author TOM
 */
class ErrorMailer {
    
    private $mailer;
    private $message;
    
    public function __construct() {
        $this->mailer = new \Nette\Mail\SmtpMailer([
        'host' => 'mail.hukot.net',
        'username' => 'error@tenisbk.cz',
        'password' => 'iL8duO6lH0apHP8vcPA1',
        'secure' => 'ssl',
        ]);
    }
    
    public function setMessage($subject, $content)
    { 
        $this->message = new \Nette\Mail\Message;
        $this->message->setSubject($subject);
        $this->message->setBody($content);
    }
    
    public function setSenderReceiver($sender, $receiver)
    {
        $this->message->addTo($receiver);
        $this->message->setFrom($sender);
    }
    
    public function sendMessage()
    {
        $this->mailer->send($this->message);
    }
}
