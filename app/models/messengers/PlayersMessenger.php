<?php

/**
 * Třída pro zpřístupnění členských proměnných modelové třídy Player
 * Realizovaná formou návrhového typu přepravka
 *
 * @category Model_Messengers
 * @subcat
 * @package  Tennis_Competitions_Blansko
 * @author   Tomáš Kopecký <kopeck32@student.vspj.cz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://www.tenisbk.cz
 */

namespace App\Model\Entity\Messenger;

use App\Model\Entity\SportEntity\Players;

/**
 * Třída PlayerMessenger
 *
 * @author Tomáš Kopecký
 */
class PlayersMessenger {

    public
            $id,
            $name,
            $sex,
            /*  OLD SOLUTION WITH THE BIRTH NUMBER
             * birthNumber,
             */
            $birthYear,
            $hand,
            $height,
            $weight,
            $slug,
            $descriptions;

    public function __construct(Players $player) {
        $this->id = $player->getId();
        $this->name = $player->getName();
        $this->sex = $player->getSex();
        $this->birthYear = $player->getBirthYear();
        $this->hand = $player->getHand();
        $this->height = $player->getHeight();
        $this->weight = $player->getWeight();
        $this->slug = $player->getSlug();
        $this->descriptions = $player->getDescriptions();
    }

}
