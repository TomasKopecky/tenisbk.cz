<?php

namespace App\Module;
use Nette\Http\IRequest;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminMailer
 *
 * @author TOM
 */
class AdminMailer {

    private $mailer;
    private $message;
    private $httpRequest;

    public function __construct(IRequest $httpRequest) {
        $this->mailer = new \Nette\Mail\SmtpMailer([
            'host' => 'mail.hukot.net',
            'username' => 'admin@tenisbk.cz',
            'password' => 'ea7560c01ba548a5e8ae1ba5d33cdaee',
            'secure' => 'ssl',
        ]);
        $this->httpRequest = $httpRequest;
    }

    public function setMessage($subject, $content, $purpose /*'contact' or 'signUp'*/) {
        $parameters = [
            'heading' => $subject,
            'text' => $content,
            'basePath' => $this->httpRequest->getUrl()->getBaseUrl()
        ];
        $latte = new \Latte\Engine;
        $this->message = new \Nette\Mail\Message;
        if ($purpose == 'signUp'){
        $this->message->setHtmlBody($latte->renderToString(__DIR__ . '/regEmail.latte', $parameters));
        }
        if ($purpose == 'contact'){
        $this->message->setHtmlBody($latte->renderToString(__DIR__ . '/contactEmail.latte', $parameters));
        }
        $this->message->setSubject($subject);
    }

    public function setSenderReceiver($sender, $receiver) {
        $this->message->addTo($receiver);
        $this->message->setFrom($sender);
    }

    public function sendMessage() {
        $this->mailer->send($this->message);
    }

}
