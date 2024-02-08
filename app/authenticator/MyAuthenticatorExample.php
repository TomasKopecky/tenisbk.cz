<?php

use Nette\Security as NS;
use App\Model\Entity\UserEntity\LogsList;
use App\Model\Entity\UserEntity\Users;
use App\Model\Entity\UserEntity\Events;

// change the MyAuthenticatorExample class name to MyAuthenticator when running the app
class MyAuthenticatorExample implements NS\IAuthenticator {

    private $database;
    private $log;
    private $user,
            $event;

    function __construct(Nette\Database\Context $database, Users $user, Events $event, LogsList $log) {
        $this->database = $database;
        $this->user = $user;
        $this->event = $event;
        $this->log = $log;
    }

    function authenticate(array $credentials) {
        list($username, $password) = $credentials;

        $row = $this->database->query('SELECT * FROM .......')>fetch(); // implement your sql query
        if (!$row) {
            $this->user->setUsername($username);
            //$this->user->setPassword($password);
            $this->log->setUnsuccessfulLoginLog($this->user);
            throw new NS\AuthenticationException();
        }

        if (!NS\Passwords::verify($password, $row->passwordDbColumn)) { // set your password column db name, the passwordDbColumn is just an example
            $this->user->setId($row->userIdDbColumn); // set your user id db column, the userIdDbColumn is just an example
            $this->log->setUnsuccessfulLoginLog($this->user);
            throw new NS\AuthenticationException();
        }

        // set the right db columns, the userIdDbColumn, userRoleNameDbColumn, nameDbColumn, usernameDbColumn and userIdRoleDbColumn are just examples
        return new NS\Identity($row->userIdDbColumn, $row->userRoleNameDbColumn, ['jmeno' => $row->nameDbColumn, 'uzivatelske_jmeno' => $row->usernameDbColumn, 'id_role' => $row->userIdRoleDbColumn]);
    }

}
