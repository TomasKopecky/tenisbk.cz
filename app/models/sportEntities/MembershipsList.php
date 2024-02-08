<?php

namespace App\Model\Entity\SportEntity;

use App\Model\Entity\SportEntity\Memberships;

/**
 * Description of PlayerStats
 *
 * @author TOM
 */
class MembershipsList extends Memberships {

    private $membershipsList = array();

    private function setMembershipsList($membershipsData) {
        foreach ($membershipsData as $membership) {
            $newMembership = new Memberships($this->database);
            $newMembership->setMembership($membership);
            $this->membershipsList[] = $newMembership;
        }
    }

    public function calcMembershipsList() {
        try {
            $values = $this->readMembershipsList();
            $this->setMembershipsList($values);
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getMembershipsList() {
        return $this->membershipsList;
    }

    private function readMembershipsList() {
        return $this->database->query('SELECT * FROM pusobeni NATURAL JOIN klub NATURAL JOIN soutez ORDER BY id_pusobeni')->fetchAll();
    }

    public function getNewInstance() {
        return new self($this->database);
    }

}

