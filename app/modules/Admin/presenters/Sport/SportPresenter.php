<?php

/**
 * Třída editačního presenteru modulu Admin - přístupná pouze administrátorům
 * úprava tenisové databáze a databáze k webu (články, logy, atd.)
 *
 * @category Admin_Module
 * @subcat
 * @package  Tennis_Competitions_Blansko
 * @author   Tomáš Kopecký <kopeck32@student.vspj.cz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://www.tenisbk.cz
 */

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use App\BasicLayoutPresenters\BasicPresenterLayout;
use App\Model\Entity\SportEntity\PlayersList;
use App\Model\Entity\SportEntity\RegistrationsList;
use App\Model\Entity\SportEntity\ClubsList;
use App\Model\Entity\SportEntity\MembershipsList;
use App\Model\Entity\SportEntity\CompetitionsList;
use App\Model\Entity\SportEntity\CompSystemsList;
use App\Model\Entity\SportEntity\PlaysTableList;
use App\Model\Entity\SportEntity\MatchesTableList;
use App\Module\ErrorMailer;

class SportPresenter extends BasicPresenterLayout {

    private $playersList,
            $clubsList,
            $registrationsList,
            $membershipsList,
            $competitionsList,
            $compSystemsList,
            $playsTableList,
            $matchesTableList,
            $playFormInputYear,
            $playFormInputComp,
            $operation,
            $ajax = false,
            $protectedSites = array('hraci', 'kluby', 'souteze', 'zapasyUprava', 'utkaniNove', 'utkaniUprava'),
            $protectedFunctions = array('delete'),
            $mailer;

    /**
     * Předání závislosti formou inject pro továrnu PlayerForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \PlayerForm @inject
     */
    public $playerFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu RegForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \RegForm @inject
     */
    public $regFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu clubForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \ClubForm @inject
     */
    public $clubFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu membershipForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \MembershipForm @inject
     */
    public $membershipFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu membershipForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \CompetitionForm @inject
     */
    public $competitionFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu membershipForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \CompSystemForm @inject
     */
    public $compSystemFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu PlayerForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \PlayForm @inject
     */
    public $playFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu PlayerForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \SinglesMatchForm @inject
     */
    public $singlesMatchFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu PlayerForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \DoublesMatchForm @inject
     */
    public $doublesMatchFormFactory;

    /**
     * Metoda pro zjištění identity přihlášeného uživatele
     *
     * @return void
     */
    public function __construct(PlayersList $playersList, RegistrationsList $registrationsList, ClubsList $clubsList, CompetitionsList $competitionsList, MembershipsList $membershipsList, CompSystemsList $compSystemsList, PlaysTableList $playsTableList, MatchesTableList $matchesTableList, ErrorMailer $mailer) {
        parent::__construct();
        $this->playersList = $playersList;
        $this->registrationsList = $registrationsList;
        $this->clubsList = $clubsList;
        $this->membershipsList = $membershipsList;
        $this->competitionsList = $competitionsList;
        $this->compSystemsList = $compSystemsList;
        $this->playsTableList = $playsTableList;
        $this->matchesTableList = $matchesTableList;
        $this->mailer = $mailer;
    }

    public function startup() {
        parent::startup();
        $this->checkLogin();
        if (in_array($this->getAction(), $this->protectedSites)) {
            $this->checkRoles(array('Admin', 'Správce'));
        }
        if ($this->isSignalReceiver($this) && strpos($this->getSignal()[1], 'delete') !== false) {
            try {
                if (!$this->user->isInRole('Admin')) {
                    $this->flashMessage('Mazání entit může provádět pouze administrátor', 'error');
                    $this->redirect($this->getAction());
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('default');
            }
        }
        $session = $this->getSession();
        $sessionSection = $session->getSection('prevSitesVars');
        $sessionSection->setExpiration(0);
        $this->template->prevSite = $sessionSection->previousSite;
        $this->template->entId = $sessionSection->entityId;
        $this->template->delOp = $sessionSection->deleteOperation;
        if (strpos($this->getAction(), 'utkani') === false && strpos($this->getAction(), 'zapas') === false) {
            if (isset($sessionSection->previousSite) || isset($sessionSection->session->entityId)) {
                unset($sessionSection->previousSite);
                unset($sessionSection->entityId);
                unset($sessionSection->deleteOperation);
            }
        }
    }

    /*
     * Výpis všech hráčů v databázi pro úpravy
     *
     * @return void
     */

    public function renderHraci() {
        $this->playersList->calcPlayersList();
        $this->template->players = $this->playersList->getPlayersList();
    }

    /**
     * Vložení nového hráče
     *
     * @param array $entries
     */
    public function renderHraciNovy() {
        //$this->template->entries = $entries;
        $this->template->formName = "insertPlayerForm";
    }

    /**
     * Vykreslení informací o hráči, v případě, že existuje
     *
     * @param type $id
     *
     * @return void
     */
    public function renderHraciUprava($id) {
        if (is_null($this->playersList->getName())) {
            $this->playersList->setId($id);
            $this->playersList->calcPlayer();
        }
        if (empty($this->playersList->getName())) {
            $this->presenter->flashMessage('Hráč zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:hraci');
        }
        $this->template->formName = "updatePlayerForm";
    }

    /**
     * Vykreslení všech registrací hráčů v klubech
     */
    public function renderRegistrace() {
        $this->registrationsList->calcRegistrationsList();
        $this->template->registrations = $this->registrationsList->getRegistrationsList();
    }

    /**
     * Výpis všech hráčů pro výběr k registraci
     * Výpis všech klubů pro výběr k registraci
     */
    public function renderRegistraceNova() {
        $this->template->formName = "insertRegForm";
    }

    /**
     * Vykreslení registrace zadaného id, v případě, že existuje
     * Výpis všech hráčů pro výběr k registraci
     * Výpis všech klubů pro výběr k registraci
     *
     * @param type $id
     */
    public function renderRegistraceUprava($id) {
        if (is_null($this->registrationsList->getDateSince())) {
            $this->registrationsList->setId($id);
            $this->registrationsList->calcRegistration();
        }
        if (empty($this->registrationsList->getDateSince())) {
            $this->presenter->flashMessage('Registrace zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:registrace');
        }
        $this->template->formName = "updateRegForm";
    }

    /**
     * Výpis všech klubů v databázi 
     *
     * @return type void
     */
    public function renderKluby() {
        $this->clubsList->calcClubsList();
        $this->template->clubs = $this->clubsList->getClubsList();
    }

    /**
     * Vložení nového klubu
     * 
     */
    public function renderKlubyNovy() {
        $this->template->formName = "insertClubForm";
    }

    /**
     * 
     * @param type $id
     */
    public function renderKlubyUprava($id) {
        if (is_null($this->clubsList->getName())) {
            $this->clubsList->setId($id);
            $this->clubsList->calcClub();
        }
        if (empty($this->clubsList->getName())) {
            $this->presenter->flashMessage('Klub zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:kluby');
        }
        $this->template->formName = "updateClubForm";
    }

    /**
     * Výpis všech členství klubů v soutěžích v databázi 
     *
     * @return type void
     */
    public function renderPusobeni() {
        $this->membershipsList->calcMembershipsList();
        $this->template->memberships = $this->membershipsList->getMembershipsList();
    }

    /**
     * Vložení nového působení klubu v soutěži
     * 
     * @return type void
     */
    public function renderPusobeniNove() {
        $this->template->formName = "insertMembershipForm";
    }

    /**
     * Úprava působení klubu v soutěži
     * 
     * @param type $id
     */
    public function renderPusobeniUprava($id) {
        if (is_null($this->membershipsList->getDateSince())) {
            $this->membershipsList->setId($id);
            $this->membershipsList->calcMembership();
        }
        if (empty($this->membershipsList->getDateSince())) {
            $this->presenter->flashMessage('Působení zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:pusobeni');
        }
        $this->template->formName = "updateMembershipForm";
    }

    public function renderSystemy() {
        $this->compSystemsList->calcCompSystemsList();
        $this->template->compSystemsList = $this->compSystemsList->getCompSystemsList();
    }

    /**
     * Vložení nového působení klubu v soutěži
     * 
     * @return type void
     */
    public function renderSystemyNovy() {
        $this->template->formName = "insertCompSystemForm";
    }

    /**
     * Úprava působení klubu v soutěži
     * 
     * @param type $id
     */
    public function renderSystemyUprava($id) {
        if (is_null($this->compSystemsList->getSeason())) {
            $this->compSystemsList->setId($id);
            $this->compSystemsList->calcCompSystem();
        }
        if (empty($this->compSystemsList->getSeason())) {
            $this->presenter->flashMessage('Systém zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:systemy');
        }
        $this->template->formName = "updateCompSystemForm";
    }

    /**
     * Výpis všech soutěží v databázi 
     *
     * @return type void
     */
    public function renderSouteze() {
        $this->competitionsList->calcCompetitionsList();
        $this->template->competitions = $this->competitionsList->getCompetitionsList();
    }

    /**
     * Vložení nové soutěže
     * 
     * @return type void
     */
    public function renderSoutezeNova() {
        $this->template->formName = "insertCompetitionForm";
    }

    /**
     * Úprava soutěže
     * 
     * @param type $id
     */
    public function renderSoutezeUprava($id) {
        if (is_null($this->competitionsList->getName())) {
            $this->competitionsList->setId($id);
            $this->competitionsList->calcCompetitions();
        }
        if (empty($this->competitionsList->getName())) {
            $this->presenter->flashMessage('Soutěž zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:souteze');
        }
        //$this->template->membership = $this->idMembershipCheck($id, 'Sport:souteze');
        $this->template->formName = "updateCompetitionForm";
        //$this->template->clubs = $this->postgre_tennis_db->getAllClubs();
        //$this->template->competitions = $this->postgre_tennis_db->getAllCompetitions();
    }

    public function renderUtkani() {
        $this->playsTableList->calcPlaysTableList();
        $this->template->playsTableList = $this->playsTableList->getPlaysTableList();
        $this->getSession('prevSitesVars')->previousSite = $this->getAction();
        unset($this->getSession('prevSitesVars')->deleteOperation);
        unset($this->getSession('prevSitesVars')->entityId);
    }

    /**
     * Výpis všech hráčů pro výběr k registraci
     * Výpis všech klubů pro výběr k registraci
     */
    public function renderUtkaniNove() {
        $this->template->formName = "insertPlayForm";
        $this->template->ajax = $this->ajax;
    }

    /**
     * Vykreslení registrace zadaného id, v případě, že existuje
     * Výpis všech hráčů pro výběr k registraci
     * Výpis všech klubů pro výběr k registraci
     *
     * @param type $id
     */
    public function renderUtkaniUprava($id) {
        if (is_null($this->playsTableList->getRound())) {
            $this->playsTableList->setId($id);
            $this->playsTableList->calcPlaysTable();
        }
        if (empty($this->playsTableList->getClubHome())) {
            $this->presenter->flashMessage('Utkání zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:utkani');
        }
        $this->template->ajax = true;
        if (is_null($this->playFormInputComp)) {
            $this->playFormInputComp = $this->playsTableList->getCompetition()->getId();
            $this->playFormInputYear = $this->playsTableList->getSeason();
        }
        $this->template->formName = "updatePlayForm";
        $this->getSession('prevSitesVars')->entityId = $id;
    }

    public function renderUtkaniZapasy($id) {
        if (is_null($this->playsTableList->getRound())) {
            $this->playsTableList->setId($id);
            $this->playsTableList->calcPlaysTable();
        }
        if (empty($this->playsTableList->getRound())) {
            if ($this->getSession('prevSitesVars')->deleteOperation !== 'playDelete') {
                $this->presenter->flashMessage('Utkání zadaného ID není v databázi', 'error');
            }
            $this->presenter->redirect(':Admin:Sport:utkani');
        }
        unset($this->getSession('prevSitesVars')->deleteOperation);
        $this->matchesTableList->setPlay($this->playsTableList);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
        $this->template->playsTable = $this->playsTableList;
        $this->getSession('prevSitesVars')->previousSite = $this->getAction();
    }

    public function renderZapasy() {
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
        $this->getSession('prevSitesVars')->previousSite = $this->getAction();
        unset($this->getSession('prevSitesVars')->entityId);
    }

    /**
     * Výpis všech hráčů pro výběr k registraci
     * Výpis všech klubů pro výběr k registraci
     */
    public function renderZapasyNovyDvouhraMuzi($idPlay) {
        $this->playExistCheck($idPlay);
        $this->matchesTableList->setPlay($this->playsTableList);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
        $this->template->playsTable = $this->playsTableList;
        $this->operation = 'insert';
        $this->template->formName = "singlesMatchForm";
        $this->template->matchType = "Dvouhra";
    }

    private function playExistCheck($idPlay) {
        if (is_null($this->playsTableList->getRound())) {
            $this->playsTableList->setId($idPlay);
            $this->playsTableList->calcPlaysTable();
        }
        if (empty($this->playsTableList->getRound())) {
            $this->presenter->flashMessage('Utkání zadaného ID není v databázi', 'error');
            $this->presenter->redirect(':Admin:Sport:utkani');
        }
    }

    public function renderZapasyNovyDvouhraZeny($idPlay) {
        $this->playExistCheck($idPlay);
        $this->matchesTableList->setPlay($this->playsTableList);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
        $this->template->playsTable = $this->playsTableList;
        $this->operation = 'insert';
        $this->template->formName = "singlesMatchForm";
        $this->template->matchType = "Dvouhra";
    }

    public function renderZapasyNovyCtyrhraMuzi($idPlay) {
        $this->playExistCheck($idPlay);
        $this->matchesTableList->setPlay($this->playsTableList);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
        $this->template->playsTable = $this->playsTableList;
        $this->operation = 'insert';
        $this->template->formName = "doublesMatchForm";
        $this->template->matchType = "Čtyřhra";
    }

    public function renderZapasyNovyCtyrhraMix($idPlay) {
        $this->playExistCheck($idPlay);
        $this->matchesTableList->setPlay($this->playsTableList);
        $this->matchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $this->matchesTableList->getMatchesTableList();
        $this->template->playsTable = $this->playsTableList;
        $this->operation = 'insert';
        $this->template->formName = "doublesMatchForm";
        $this->template->matchType = "Čtyřhra";
    }

    /**
     * Vykreslení registrace zadaného id, v případě, že existuje
     * Výpis všech hráčů pro výběr k registraci
     * Výpis všech klubů pro výběr k registraci
     *
     * @param type $id
     */
    public function renderZapasyUprava($id) {
        if (is_null($this->matchesTableList->getRound())) {
            $this->matchesTableList->setId($id);
            $this->matchesTableList->calcMatchesTable();
        }
        if (empty($this->matchesTableList->getRound())) {
            if ($this->getSession('prevSitesVars')->deleteOperation !== 'matchDelete') {
                $this->presenter->flashMessage('Zápas zadaného ID není v databázi', 'error');
            }
            $this->presenter->redirect(':Admin:Sport:zapasy');
        }
        unset($this->getSession('prevSitesVars')->deleteOperation);
        $this->template->matchType = $this->matchesTableList->getMatchTypeName();
        $this->playsTableList->setId($this->matchesTableList->getPlay()->getId());
        $this->playsTableList->calcPlaysTable();
        $this->template->playsTable = $this->playsTableList;
        $this->template->formName = $this->matchesTableList->getMatchTypeId() == 1 || $this->matchesTableList->getMatchTypeId() == 3 ? "singlesMatchForm" : "doublesMatchForm";
        $newMatchesTableList = $this->matchesTableList->getNewInstance();
        $newMatchesTableList->setPlay($this->playsTableList);
        $newMatchesTableList->calcMatchesTableList();
        $this->template->matchesTableList = $newMatchesTableList->getMatchesTableList();
        $this->operation = 'update';
        //$this->getSession('prevSitesVars')->previousSite = $this->getAction();
    }

    /**
     * Vloží a obslouží formulář pro úpravu hráčů
     *
     * @return type
     */
    public function createComponentUpdatePlayerForm() {
        $form = $this->playerFormFactory->create('update', $this, $this->playersList); // metoda create z továrny PlayerForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava hráče \'' . $form['jmeno']->getValue() . '\' provedena', 'info');
            $this->redirect('Sport:hraci');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytváření hráčů
     *
     * @return type
     */
    public function createComponentInsertPlayerForm() {
        $form = $this->playerFormFactory->create('insert', $this, null); // metoda create z továrny PlayerForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení hráče \'' . $form['jmeno']->getValue() . '\' provedeno', 'info');
            $this->redirect('Sport:hraci');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro úpravu registrací
     *
     * @return type
     */
    public function createComponentUpdateRegForm() {
        $form = $this->regFormFactory->create('update', $this, $this->registrationsList); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava registrace hráče \'' . $form['id_hrac']->getSelectedItem() . '\' v klubu \'' . $form['id_klub']->getSelectedItem() . '\' provedena', 'info');
            $this->redirect('Sport:registrace');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytvoření registrace
     *
     * @return type
     */
    public function createComponentInsertRegForm() {
        $form = $this->regFormFactory->create('insert', $this, null); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Registrace hráče \'' . $form['id_hrac']->getSelectedItem() . '\' v klubu \'' . $form['id_klub']->getSelectedItem() . '\' vložena', 'info');
            $this->redirect('Sport:registrace');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro úpravu klubu
     *
     * @return type
     */
    public function createComponentUpdateClubForm() {
        $form = $this->clubFormFactory->create('update', $this, $this->clubsList); // metoda create z továrny ClubForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava klubu \'' . $form['nazev']->getValue() . '\' provedena', 'info');
            $this->redirect('Sport:kluby');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytváření klubu
     *
     * @return type
     */
    public function createComponentInsertClubForm() {
        $form = $this->clubFormFactory->create('insert', $this, null); // metoda create z továrny ClubForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení klubu \'' . $form['nazev']->getValue() . '\' provedeno', 'info');
            $this->redirect('Sport:kluby');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro úpravu působení
     *
     * @return type
     */
    public function createComponentUpdateMembershipForm() {
        $form = $this->membershipFormFactory->create('update', $this, $this->membershipsList); // metoda create z továrny MembershipForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava členství klubu \'' . $form['id_klub']->getSelectedItem() . '\' v soutěži \'' . $form['id_soutez']->getSelectedItem() . '\' provedena', 'info');
            $this->redirect('Sport:pusobeni');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytváření působení
     *
     * @return type
     */
    public function createComponentInsertMembershipForm() {
        $form = $this->membershipFormFactory->create('insert', $this); // metoda create z továrny MembershipForm pro insert metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení členství klubu ' . $form['id_klub']->getSelectedItem() . '\' v soutěži \'' . $form['id_soutez']->getSelectedItem() . '\' provedena', 'info');
            $this->redirect('Sport:pusobeni');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro úpravu soutěže
     *
     * @return type
     */
    public function createComponentUpdateCompetitionForm() {
        $form = $this->competitionFormFactory->create('update', $this, $this->competitionsList); // metoda create z továrny CompetitionForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava soutěže \'' . $form['jmeno']->getValue() . '\' provedena', 'info');
            $this->redirect('Sport:souteze');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytváření soutěže
     *
     * @return type
     */
    public function createComponentInsertCompetitionForm() {
        $form = $this->competitionFormFactory->create('insert', $this); // metoda create z továrny CompetitionForm pro insert metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení soutěže \'' . $form['jmeno']->getValue() . '\' provedena', 'info');
            $this->redirect('Sport:souteze');
        };
        return $form;
    }

    public function createComponentUpdateCompSystemForm() {
        $form = $this->compSystemFormFactory->create('update', $this, $this->compSystemsList); // metoda create z továrny CompSystemForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava systému soutěže \'' . $form['id_soutez']->getSelectedItem() . '\' v ročníku \'' . $form['rocnik']->getValue() . '\' provedena', 'info');
            $this->redirect('Sport:systemy');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytváření působení
     *
     * @return type
     */
    public function createComponentInsertCompSystemForm() {
        $form = $this->compSystemFormFactory->create('insert', $this); // metoda create z továrny CompSystemForm pro insert metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení systému soutěže \'' . $form['id_soutez']->getSelectedItem() . '\' v ročníku \'' . $form['rocnik']->getValue() . '\' provedeno', 'info');
            $this->redirect('Sport:systemy');
        };
        return $form;
    }

    public function createComponentUpdatePlayForm() {
        $form = $this->playFormFactory->create('update', $this, $this->playsTableList, $this->playFormInputYear, $this->playFormInputComp); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava utkání klubů \'' . $form['klub_domaci']->getSelectedItem() . '\' a \'' . $form['klub_hoste']->getSelectedItem() . '\' úspěšně provedena', 'info');
            try {
                if ($this->getSession('prevSitesVars')->previousSite !== 'utkani') {
                    $this->redirect('Sport:' . $this->getSession('prevSitesVars')->previousSite, $this->getSession('prevSitesVars')->entityId);
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('Sport:utkani');
            }
            $this->redirect('Sport:utkani');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytvoření registrace
     *
     * @return type
     */
    public function createComponentInsertPlayForm() {
        $form = $this->playFormFactory->create('insert', $this, null, $this->playFormInputYear, $this->playFormInputComp); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení utkání klubů \'' . $form['klub_domaci']->getSelectedItem() . '\' a \'' . $form['klub_hoste']->getSelectedItem() . '\' úspěšně provedeno', 'info');
            $this->redirect('Sport:utkani');
        };
        return $form;
    }

    public function createComponentUpdateMatchForm() {
        $form = $this->matchFormFactory->create('update', $this, $this->matchesTableList, $this->matchFormInputComp); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava zápasu mezi hráči ' . $form['klub_domaci']->getSelectedItem() . ' a ' . $form['klub_hoste']->getSelectedItem() . '\' úspěšně provedena', 'info');
            try {
                if ($this->getSession('prevSitesVars')->previousSite !== 'zapasy') {
                    $this->redirect('Sport:' . $this->getSession('prevSitesVars')->previousSite, $this->getSession('prevSitesVars')->entityId);
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('Sport:zapasy');
            }
            $this->redirect('Sport:zapasy');
        };
        return $form;
    }

    /**
     * Vloží a obslouží formulář pro vytvoření registrace
     *
     * @return type
     */
    public function createComponentSinglesMatchForm() {
        $form = $this->singlesMatchFormFactory->create($this->operation, $this, $this->playsTableList, null); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $text = ($form['zapas_kontumace_domaci']->getValue() || $form['zapas_kontumace_hoste']->getValue()) ? 'v rámci kontumace' : 'mezi hráči ' . $form['hrac1_domaci']->getSelectedItem() . ' a ' . $form['hrac1_hoste']->getSelectedItem();
            $this->flashMessage(($this->operation == 'insert' ? 'Vložení' : 'Úprava') . ' zápasu ' . $text . ' úspěšně' . ($this->operation == 'insert' ? ' provedeno' : ' provedena'), 'info');
            $idPlay = $this->presenter->getParameter('idPlay') ?? $this->playsTableList->getId();
            try {
                if ($this->getSession('prevSitesVars')->previousSite !== 'zapasy') {
                    $this->redirect('Sport:' . $this->getSession('prevSitesVars')->previousSite, $idPlay);
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('Sport:zapasy');
            }
            $this->redirect('Sport:zapasy');
        };
        return $form;
    }

    public function createComponentDoublesMatchForm() {
        $form = $this->doublesMatchFormFactory->create($this->operation, $this, $this->playsTableList, null); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $text = ($form['zapas_kontumace_domaci']->getValue() || $form['zapas_kontumace_hoste']->getValue()) ? 'v rámci kontumace' : 'mezi hráči ' . $form['hrac1_domaci']->getSelectedItem() . ', ' . $form['hrac2_domaci']->getSelectedItem() . ' a ' . $form['hrac1_hoste']->getSelectedItem() . ', ' . $form['hrac2_hoste']->getSelectedItem();
            $this->flashMessage(($this->operation == 'insert' ? 'Vložení' : 'Úprava') . ' zápasu ' . $text . ' úspěšně' . ($this->operation == 'insert' ? ' provedeno' : ' provedena'), 'info');
            $idPlay = $this->presenter->getParameter('idPlay') ?? $this->playsTableList->getId();
            try {
                if ($this->getSession('prevSitesVars')->previousSite !== 'zapasy') {
                    $this->redirect('Sport:' . $this->getSession('prevSitesVars')->previousSite, $idPlay);
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('Sport:zapasy');
            }
            $this->redirect('Sport:zapasy');
        };
        return $form;
    }

    public function handlePlayInput($seasonYear, $competition) { // handle na ajax událost (změna roku) v šabloně s detaily o hráči
        $this->playFormInputYear = $seasonYear;
        $this->playFormInputComp = $competition;

        if ($this->isAjax()) {
            $this->ajax = true;
            $this->redrawControl('ajaxPlayForm');
        }
    }

    public function handleDeletePlayer($id) {
        $this->playersList->setId($id);
        $this->playersList->calcPlayer();
        try {
            $this->playersList->deletePlayer();
            $this->playersList->logDelete();
            $this->flashMessage('Vymazání hráče ' . $this->playersList->getName() . ' úspěšně provedeno', 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání hráče ' . $this->playersList->getName() . '. Je třeba neprve třeba vymazat jeho registraci a všechny zápasy, ve kterých hráč v daném rozmezí a klubu působí', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání hráči', 'Nastala nespecifikovaná chyba při editaci hráče - mazání. Jednalo se o hráče s id ' . $this->playersList->getId() . ' a jménem '. $this->playersList->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Nezjištěná chyba při mazání hráče ' . $this->playersList->getName() . ' - kontaktuje administrátora', 'error');
        } finally {
            $this->redirect('hraci');
        }
    }

    public function handleDeleteRegistration($id) {
        $this->registrationsList->setId($id);
        $this->registrationsList->calcRegistration();
        try {
            $this->registrationsList->deleteRegistration();
            $this->registrationsList->logDelete();
            $this->flashMessage('Mazání registrace hráče ' . $this->registrationsList->getPlayer()->getName() . ' v klubu ' . $this->registrationsList->getClub()->getName() . ' proběhlo úspěšně', 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání registrace hráče ' . $this->registrationsList->getPlayer()->getName() . ' v klubu ' . $this->registrationsList->getClub()->getName() . '. Je třeba nejprve vymazat všechny zápasy, ve kterých hráč v daném rozmezí a klubu působí', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání registrace hráče', 'Nastala nespecifikovaná chyba při editaci registrace hráče - mazání. Jednalo se o registraci s id ' . $this->registrationsList->getId() . ' pro hráče s id '. $this->registrationsList->getPlayer()->getId() . ' a jménem ' . $this->registrationsList->getPlayer()->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Nezjištěná chyba při mazání registrace hráče ' . $this->registrationsList->getPlayer()->getName() . ' v klubu ' . $this->registrationsList->getClub()->getName() . ' - kontaktujte administátora', 'error');
        } finally {
            $this->redirect('registrace');
        }
    }

    public function handleDeleteClub($id) {
        $this->clubsList->setId($id);
        $this->clubsList->calcClub();
        try {
            $this->clubsList->deleteClub();
            $this->clubsList->logDelete();
            $this->flashMessage('Vymazání klubu ' . $this->clubsList->getName() . ' úspěšně provedeno', 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání klubu ' . $this->clubsList->getName() . '. Je třeba neprve třeba vymazat jeho působení v soutěži a všechna utkání, ve kterých je zařazen', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání klubu', 'Nastala nespecifikovaná chyba při editaci klubu - mazání. Jednalo se o klub s id ' . $this->clubsList->getId() . ' a názvem '. $this->clubsList->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Nezjištěná chyba při mazání klubu ' . $this->clubsList->getName() . ' - kontaktujte administrátora', 'error');
        } finally {
            $this->redirect('kluby');
        }
    }

    public function handleDeleteMembership($id) {
        $this->membershipsList->setId($id);
        $this->membershipsList->calcMembership();
        try {
            $this->membershipsList->deleteMembership();
            $this->membershipsList->logDelete();
            $this->flashMessage('Mazání působení klubu ' . $this->membershipsList->getClub()->getName() . ' v soutezi ' . $this->membershipsList->getCompetition()->getName() . ' proběhlo úspěšně', 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání působení klubu ' . $this->membershipsList->getCompetition()->getName() . ' v klubu ' . $this->membershipsList->getClub()->getName() . '. Je třeba nejprve vymazat všechna utkání, ve kterých klub působí', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání klubové příslušnosti v soutěži', 'Nastala nespecifikovaná chyba při editaci klubové příslšnosti v soutěži - mazání. Jednalo se o příslušnost s id ' . $this->membershipsList->getId() . ' pro klub s názvem '. $this->membershipsList->getClub()->getName() . ' a soutěží s názvem ' . $this->membershipsList->getCompetition()->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Nezjištěná chyba při mazání působení klubu ' . $this->membershipsList->getCompetition()->getName() . ' v klubu ' . $this->membershipsList->getClub()->getName() . ' - kontaktujte administrátora', 'error');
        } finally {
            $this->redirect('pusobeni');
        }
    }

    public function handleDeleteCompetition($id) {
        $this->competitionsList->setId($id);
        $this->competitionsList->calcCompetitions();
        try {
            $this->competitionsList->deleteCompetition();
            $this->competitionsList->logDelete();
            $this->flashMessage('Vymazání soutěže ' . $this->competitionsList->getName() . ' úspěšně provedeno', 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání soutěže ' . $this->competitionsList->getName() . '. Je třeba neprve třeba vymazat všechna působení klubů v dané soutěži, která existují.', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání soutěže', 'Nastala nespecifikovaná chyba při editaci soutěže - mazání. Jednalo se o soutěž s id ' . $this->competitionsList->getId() . ' a názvem '. $this->competitionsList->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Chyba při mazání soutěže ' . $this->competitionsList->getName() . ' - kontaktujte administrátora', 'error');
        } finally {
            $this->redirect('souteze');
        }
    }

    public function handleDeleteCompSystems($id) {
        $this->compSystemsList->setId($id);
        $this->compSystemsList->calcCompSystem();
        try {
            if (is_null($this->compSystemsList->getRoundSystem())) {
                $this->flashMessage('Systém soutěží zadaného ID neexistuje, nelze jej proto vymazat', 'error');
            }
            $this->compSystemsList->deleteCompSystem();
            $this->compSystemsList->logDelete();
            $this->flashMessage('Vymazání systému soutěže ' . $this->compSystemsList->getCompetition()->getName() . ' úspěšně provedeno', 'info');
        } catch (\Nette\Database\DriverException $e) {
            //$this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->flashMessage('Chyba při mazání systému soutěže ' . $this->compSystemsList->getCompetition()->getName() . ' v ročníku ' . $this->compSystemsList->getSeason() . '. Nelze provést mazání systému soutěží - vybraná soutěž již eviduje pro daný ročník nějaká utkání, nebo je v soutěži již registrován nějaký klub. Tato utkání či působení klubů je třeba nejprve vymazat.', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání systému soutěží', 'Nastala nespecifikovaná chyba při editaci systému soutěží - mazání. Jednalo se o systém s id ' . $this->compSystemsList->getCompetition()->getId() . ' a názvem '. $this->compSystemsList->getCompetition()->getName() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Chyba při mazání systému soutěže ' . $this->compSystemsList->getCompetition()->getName() . ' v ročníku ' . $this->compSystemsList->getSeason() . ' - kontaktujte administrátora', 'error');
        } finally {
            $this->redirect('systemy');
        }
    }

    public function handleDeletePlay($id) {
        $this->playsTableList->setId($id);
        $this->playsTableList->calcPlaysTable();
        try {
            if (is_null($this->playsTableList->getClubHome())) {
                $this->flashMessage('Utkání zadaného ID neexistuje, nelze jej proto vymazat', 'error');
            }
            $this->playsTableList->deletePlay();
            $this->playsTableList->logDelete();
            $this->flashMessage('Vymazání utkání mezi týmy ' . $this->playsTableList->getClubHome()->getname() . ' a ' . $this->playsTableList->getClubVisitors()->getname() . ' úspěšně provedeno', 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Chyba při mazání utkání mezi týmy ' . $this->playsTableList->getClubHome()->getname() . ' a ' . $this->playsTableList->getClubVisitors()->getname() . ' - Nejprve je třeba vymazat všechny zápasy v daném utkání.', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání utkání', 'Nastala nespecifikovaná chyba při editaci utkání - mazání. Jednalo se o utkání s id ' . $this->playsTableList->getId() . ' mezi kluby '. $this->playsTableList->getClubHome()->getname() . ' a ' . $this->playsTableList->getClubVisitors()->getname() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Nezjištěná chyba při mazání utkání mezi týmy ' . $this->playsTableList->getClubHome()->getname() . ' a ' . $this->playsTableList->getClubVisitors()->getname() . ' - kontaktujte administrátora', 'error');
        } finally {
            $this->getSession('prevSitesVars')->deleteOperation = 'playDelete';
            try {
                if ($this->getSession('prevSitesVars')->previousSite !== 'utkani' && $this->getSession('prevSitesVars')->previousSite !== 'zapasy') {
                    $this->redirect('Sport:' . $this->getSession('prevSitesVars')->previousSite, $this->playsTableList->getId());
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('Sport:Utkani');
            }
            $this->redirect('utkani');
        }
    }

    public function handleDeleteMatch($id) {
        $this->matchesTableList->setId($id);
        $this->matchesTableList->calcMatchesTable();
        try {
            if (is_null($this->matchesTableList->getPlayerHome1())) {
                $this->flashMessage('Zápas zadaného ID neexistuje, nelze jej proto vymazat', 'error');
            }
            $this->matchesTableList->deleteMatch();
            $this->matchesTableList->logDelete();
            $text = is_null($this->matchesTableList->getPlayerHome1()->getname()) ? 'Vymazání kontumovaného zápasu úspěšně provedeno' : 'Vymazání zápasu mezi hráči ' . $this->matchesTableList->getPlayerHome1()->getname() . (is_null($this->matchesTableList->getPlayerHome2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerHome2()->getname() : NULL) . ' a ' . $this->matchesTableList->getPlayerVisitors1()->getname() . (is_null($this->matchesTableList->getPlayerVisitors2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerVisitors2()->getname() : NULL) . ' úspěšně provedeno';
            $this->flashMessage($text, 'info');
        } catch (\Nette\Database\DriverException $e) {
            $this->flashMessage('Databázová chyba při mazání zápasu mezi hráči ' . $this->matchesTableList->getPlayerHome1()->getname() . (is_null($this->matchesTableList->getPlayerHome2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerHome2()->getname() : NULL) . ' a ' . $this->matchesTableList->getPlayerVisitors1()->getname() . (is_null($this->matchesTableList->getPlayerVisitors2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerVisitors2()->getname() : NULL) . ' - kontaktujte administrátora.', 'error');
        } catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání zápasu', 'Nastala nespecifikovaná chyba při editaci zápasu - mazání. Jednalo se o zápas s id ' . $this->matchesTableList->getId() . ' mezi hráči ' . $this->matchesTableList->getPlayerHome1()->getname() . (is_null($this->matchesTableList->getPlayerHome2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerHome2()->getname() : NULL) . ' a ' . $this->matchesTableList->getPlayerVisitors1()->getname() . (is_null($this->matchesTableList->getPlayerVisitors2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerVisitors2()->getname() : NULL) . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->flashMessage('Nezjištěná chyba při mazání zápasu mezi hráči ' . $this->matchesTableList->getPlayerHome1()->getname() . (is_null($this->matchesTableList->getPlayerHome2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerHome2()->getname() : NULL) . ' a ' . $this->matchesTableList->getPlayerVisitors1()->getname() . (is_null($this->matchesTableList->getPlayerVisitors2()->getname()) == false ? ', ' . $this->matchesTableList->getPlayerVisitors2()->getname() : NULL) . ' - kontaktujte administrátora', 'error');
        } finally {
            $this->getSession('prevSitesVars')->deleteOperation = 'matchDelete';
            try {
                if ($this->getSession('prevSitesVars')->previousSite !== 'zapasy') {
                    $this->redirect('Sport:' . $this->getSession('prevSitesVars')->previousSite, $this->matchesTableList->getPlay()->getId());
                }
            } catch (\Nette\Application\UI\InvalidLinkException $e) {
                $this->redirect('zapasy');
            }
            $this->redirect('zapasy');
        }
    }

}
