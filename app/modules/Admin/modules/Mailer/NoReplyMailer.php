<?php

namespace App\Module;

use Nette\Http\IRequest;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NoReplyMailer
 *
 * @author TOM
 */
class NoReplyMailer {

    private $mailer;
    private $message;
    private $httpRequest;

    public function __construct(IRequest $httpRequest) {
        $this->mailer = new \Nette\Mail\SmtpMailer([
            'host' => 'mail.hukot.net',
            'username' => 'neodpovidat@tenisbk.cz',
            'password' => 'Qfy2Zr5VXi5ANhg',
            'secure' => 'ssl',
        ]);
        $this->httpRequest = $httpRequest;
    }

    public function setMessage($subject, $content, $registrationMessage = false) {
        $parameters = [
            'heading' => $subject,
            'text' => $content,
            'basePath' => $this->httpRequest->getUrl()->getBaseUrl()
        ];
        $latte = new \Latte\Engine;
        $this->message = new \Nette\Mail\Message;
        if ($registrationMessage) {
            $this->message->setHtmlBody($latte->renderToString(__DIR__ . '/regEmail.latte', $parameters));
        } else {
            $this->message->setHtmlBody($latte->renderToString(__DIR__ . '/contactEmail.latte', $parameters));
        }
        $this->message->setSubject($subject);
    }

    public function setSenderReceiver($sender, $receivers) {
        if (is_array($receivers)) {
            foreach ($receivers as $receiver) {
                $this->message->addTo($receiver);
            }
        } else {
            $this->message->addTo($receivers);
        }
        $this->message->setFrom($sender);
    }

    public function sendMessage() {
        $this->mailer->send($this->message);
    }

}
