<?php

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Registrations;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class RegistrationsList extends Registrations {

    private $registrationsList = array();

    private function setRegistrationsList($registrationsData) {
        foreach ($registrationsData as $registration) {
            $newRegistration = new Registrations($this->database);
            $newRegistration->setRegistration($registration);
            $this->registrationsList[] = $newRegistration;
        }
    }

    public function calcRegistrationsList($calcByClubAndYear = false) {
        try {
            if ($calcByClubAndYear) {
                $values = $this->readRegistrationsListByYearAndClub();
            } else {
                $values = $this->readRegistrationsList();
            }
            $this->setRegistrationsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getRegistrationsList() {
        return $this->registrationsList;
    }

    private function readRegistrationsList() {
        return $this->database->query('SELECT * FROM registrace NATURAL JOIN hrac NATURAL JOIN klub ORDER BY id_registrace')->fetchAll();
    }

    private function readRegistrationsListByYearAndClub() {
        return $this->database->query('SELECT * FROM registrace NATURAL JOIN hrac NATURAL JOIN klub WHERE ? between datum_od AND CASE WHEN datum_do IS NULL THEN NOW() ELSE datum_do END AND CASE WHEN ? = 0 THEN id_klub > 0 ELSE id_klub = ? END ORDER BY id_klub, hrac_muzi_poradi, id_registrace', '1.1.'.$this->seasonYear, $this->getClub()->getId(),$this->getClub()->getId())->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}
