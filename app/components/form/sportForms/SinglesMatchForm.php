<?php

use Nette\Application\UI\Form;
use App\Form\BasicFormService\BasicForms;
use App\Model\Entity\SportEntity\PlaysTable;
use App\Model\Entity\SportEntity\PlayersList;
use App\Model\Entity\SportEntity\MatchesTable;
use App\Model\Entity\SportEntity\Results;
use App\Model\Entity\CheckTools\ResultsCheck;
use App\Module\ErrorMailer;

class SinglesMatchForm extends BasicForms {

    private $playsTable,
            $playersList,
            $playersHome,
            $matchesTable,
            $playersVisitors,
            $setsHome,
            $setsVisitors,
            $winHome,
            $winVisitors,
            $result,
            $mailer;
    
    public function __construct(PlaysTable $playsTable, PlayersList $playersList, MatchesTable $matchesTable, ErrorMailer $mailer) {
        $this->playsTable = $playsTable;
        $this->playersList = $playersList;
        $this->matchesTable = $matchesTable;
        $this->mailer = $mailer;
    }

    public function start() {
        if (strpos($this->presenter->getAction(), 'zapasyNovy') !== false) {
            $this->playsTable->setId($this->presenter->getParameter('idPlay'));
            $this->playsTable->calcPlaysTable();
        } else {
            $this->matchesTable->setId($this->presenter->getParameter('id'));
            $this->matchesTable->calcMatchesTable();
            $this->playsTable->setId($this->matchesTable->getPlay()->getId());
            $this->playsTable->calcPlaysTable();
        }
    }

    public function getPlayersList() {
        //throw new Exception($this->presenter->getAction());
        if (strpos($this->presenter->getAction(), 'zapasyNovy') !== false) {
            $sex = strpos($this->presenter->getAction(), 'Muzi') != false ? 'M' : 'Z';
        }
        if (strpos($this->presenter->getAction(), 'zapasyUprava') !== false) {

            $sex = $this->matchesTable->getMatchTypeId() == 1 ? 'M' : 'Z';
        }
        $this->playersList->calcPlayersListByYearAndClub($this->playsTable->getClubHome()->getId(), $sex, $this->playsTable->getSeason());
        $this->playersHome = $this->playersList;
        $this->playersVisitors = $this->playersList->getNewInstance();
        $this->playersVisitors->calcPlayersListByYearAndClub($this->playsTable->getClubVisitors()->getId(), $sex, $this->playsTable->getSeason());
        //$this->playersVisitors = $this->playersList;
        //$this->playersList->calcPlayersList();
    }

    private function setPlayersSelect($form) {

        $form['hrac1_domaci']->setItems($this->setPlayersAssoc($this->playersHome->getPlayersList()));
        $form['hrac1_hoste']->setItems($this->setPlayersAssoc($this->playersVisitors->getPlayersList()));
        //$form['hrac1_domaci']->setItems(['null' => 'null']);
        //$form['hrac1_hoste']->setItems(['null' => 'null']);
    }

    public function create($operation, $presenter, $playsTable = null, $sex = null) { // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
        $this->presenter = $presenter;
        $this->operation = $operation;
        if (!is_null($playsTable)) {
            $this->playsTable = $playsTable;
        }
        $this->start();
        $this->getPlayersList();
        $form = new Form();
        
        $form->addText('datum', 'Datum zápasu')
                ->setAttribute('class', 'form-control pull-right date')
                ->setHtmlAttribute('readonly')
                ->setRequired('Zvolte datum');

        $form->addCheckbox('skrec_domaci')
                ->setAttribute('class', 'checkbox square');

        $form->addCheckbox('zapas_kontumace_domaci')
                ->setAttribute('class', 'checkbox square');

        $form->addCheckbox('skrec_hoste')
                ->setAttribute('class', 'checkbox square');

        $form->addCheckbox('zapas_kontumace_hoste')
                ->setAttribute('class', 'checkbox square');

        $this->setMatchMenOrder($form);
        $this->setPlayersInputs($form);
        $this->setPlayersSelect($form);
        $this->setResultsInputs($form);
        $form->addTextArea('zapas_info', 'Info o zápasu')
                ->setAttribute('class', 'form-control')
                //->addRule(Form::PATTERN, 'Text nesmí začínat mezerou a obsahovat jiné znaky než písmena a interpunkční znaménka)', '^[^\s][A-Z ĚŠČŘĎŤŇŽÝÁÍÉÚŮ a-z ěščřďťňžýáíéúů \s-,.!]*$')
                ->setAttribute('rows', 3)
                ->setRequired(false)
                ->setMaxLength(200);
        $form->addSubmit('matchButton', 'Uložit');
        $this->operation = 'insert';
        if (strpos($this->presenter->getAction(), 'zapasyUprava') !== false) {

            $this->setDefaults($form);
            $form->onValidate[] = [$this, 'validateForm'];
            $this->operation = 'update';
        }
        $form->onValidate[] = [$this, 'validateResults'];
        $form->onSuccess[] = array($this, $this->operation);
        return $form;
    }

    private function setPlayersInputs($form) {
        for ($i = 1; $i < 2; $i++) {
            $form->addSelect('hrac' . $i . '_domaci', 'Domácí hráči')
                    ->setAttribute('class', 'form-control')
                    ->setPrompt('Zvolte domácí hráče')
                    ->setRequired('Zvolte domácí hráče' . $i)
                    ->setOption('id', 'domaci' . $i);
            $form->addSelect('hrac' . $i . '_hoste', 'Hostující hráči')
                    ->setAttribute('class', 'form-control')
                    ->setPrompt('Zvolte hostující hráče')
                    ->setRequired('Zvolte hostující hráče' . $i)
                    ->setOption('id', 'hoste' . $i);
        }
    }

    private function setResultsInputs($form) {
        $inputs = array('set', 'tb');
        foreach ($inputs as $input) {
            for ($i = 1; $i < 4; $i++) {
                $form->addText($input . $i . '_domaci')
                        ->setValue(0)
                        ->setRequired(false)
                        ->setAttribute('class', 'form-control right')
                        ->setHtmlType('number');
                $form->addText($input . $i . '_hoste')
                        ->setValue(0)
                        ->setRequired(false)
                        ->setAttribute('class', 'form-control right')
                        ->setHtmlType('number');
                //->addRule(Form::MIN, 'Minimální hodnota zadání je 0', 0);
                if ($input == 'set') {
                    $form[$input . $i . '_domaci']->addRule(Form::RANGE, 'Zadejte hodnotu od %d do %d', [0, 7]);
                    $form[$input . $i . '_hoste']->addRule(Form::RANGE, 'Zadejte hodnotu od %d do %d', [0, 7]);
                }
                if ($input == 'tb') {
                    $form[$input . $i . '_domaci']->addRule(Form::MIN, 'Minimální hodnota zadání je %d', 0);
                    $form[$input . $i . '_hoste']->addRule(Form::MIN, 'Minimální hodnota zadání je %d', 0);
                }
            }
        }
    }
    
    private function setMatchMenOrder($form) {
        if($this->matchesTable->getMatchTypeId() != null){
            $form->addHidden('typ_zapasu',$this->matchesTable->getMatchTypeId());
        }
        else{
            $form->addHidden('typ_zapasu',$this->presenter->getAction());
        }
        if ($this->presenter->getAction() == 'zapasyNovyDvouhraMuzi' || $this->matchesTable->getMatchTypeId() == 1){
            $form->addSelect('zapas_muzi_poradi', 'Domácí hráči', [1=>1,2=>2,3=>3])
                ->setAttribute('class', 'form-control')
                ->setPrompt('Zvolte pořadí zápasu mužů')
                ->setRequired('Zvolte pořadí zápasu mužů');
        }
    }

    public function validateResults($form) {
        $values = $form->getValues();
        $this->result = new Results();
        $this->result->setFormResults($values);
        $checkResult = new ResultsCheck($this->result);
        $checkResult->setHomeLossDefault($form['zapas_kontumace_domaci']->getValue());
        $checkResult->setVisitorsLossDefault($form['zapas_kontumace_hoste']->getValue());
        $checkResult->setHomeRetire($form['skrec_domaci']->getValue());
        $checkResult->setVisitorsRetire($form['skrec_hoste']->getValue());
        $checkResult->fullCheck();
        foreach ($checkResult->getErrors() as $error) {
            $form->addError($error);
        }
        $this->setsHome = $checkResult->calcSetsWins()[0];
        $this->setsVisitors = $checkResult->calcSetsWins()[1];
        $this->winHome = $checkResult->calcWinHome();
        $this->winVisitors = $checkResult->calcWinVisitors();
        if ($checkResult->getHomeLossDefault() || $checkResult->getVisitorsLossDefault()) {
            $form['hrac1_domaci']->setValue(null);
            $form['hrac1_hoste']->setValue(null);
        }
    }

    public function validateForm($form) {
        if (
                $form['hrac1_domaci']->getValue() == $this->matchesTable->getPlayerHome1()->getId() &&
                $form['hrac1_hoste']->getValue() == $this->matchesTable->getPlayerVisitors1()->getId() &&
                $form['datum']->getValue() == (is_null($this->matchesTable->getDate()) ? null : date_format($this->matchesTable->getDate(), "d.m.Y")) &&
                $form['zapas_kontumace_domaci']->getValue() == $this->matchesTable->getLossDefaultHome() &&
                $form['zapas_kontumace_hoste']->getValue() == $this->matchesTable->getLossDefaultVisitors() &&
                $form['skrec_domaci']->getValue() == $this->matchesTable->getRetireHome() &&
                $form['skrec_hoste']->getValue() == $this->matchesTable->getRetireVisitors() &&
                $form['set1_domaci']->getValue() == $this->matchesTable->getResults()->getSet1Home() &&
                $form['set2_domaci']->getValue() == $this->matchesTable->getResults()->getSet2Home() &&
                $form['set3_domaci']->getValue() == $this->matchesTable->getResults()->getSet3Home() &&
                $form['tb1_domaci']->getValue() == $this->matchesTable->getResults()->getTb1Home() &&
                $form['tb2_domaci']->getValue() == $this->matchesTable->getResults()->getTb2Home() &&
                $form['tb3_domaci']->getValue() == $this->matchesTable->getResults()->getTb3Home() &&
                $form['set1_hoste']->getValue() == $this->matchesTable->getResults()->getSet1Visitors() &&
                $form['set2_hoste']->getValue() == $this->matchesTable->getResults()->getSet2Visitors() &&
                $form['set3_hoste']->getValue() == $this->matchesTable->getResults()->getSet3Visitors() &&
                $form['tb1_hoste']->getValue() == $this->matchesTable->getResults()->getTb1Visitors() &&
                $form['tb2_hoste']->getValue() == $this->matchesTable->getResults()->getTb2Visitors() &&
                $form['tb3_hoste']->getValue() == $this->matchesTable->getResults()->getTb3Visitors() &&
                $form['zapas_muzi_poradi']->getValue() == $this->matchesTable->getMatchMenOrder() &&
                $form['zapas_info']->getValue() == $this->matchesTable->getDescriptions()) {
            $form->addError('Ve formuláři jste neprovedli žádnou změnu');
        }
           if (
                ($form['zapas_kontumace_domaci']->getValue() || $form['zapas_kontumace_hoste']->getValue()) &&      
                $form['zapas_kontumace_domaci']->getValue() == $this->matchesTable->getLossDefaultHome() &&
                $form['zapas_kontumace_hoste']->getValue() == $this->matchesTable->getLossDefaultVisitors()
           ) {
               $form->addError('Ve formuláři jste neprovedli žádnou změnu');
           }
    }

    public function setPlayersAssoc($players) { // převede získanou tabulku klubů z databáze na asoc. pole formát "id_hrac" => "jmeno"
        $playerArray = array();
        foreach ($players as $player) {
            $playerArray[$player->getId()] = $player->getName();
        }
        $playerArray['null'] = 'null';
//$playerArray[] = ['null' => 'null'];
        return $playerArray;
    }

    protected function setDefaults($form) {
        $form->setDefaults([
            'hrac1_domaci' => $this->matchesTable->getPlayerHome1()->getId(),
            'hrac1_hoste' => $this->matchesTable->getPlayerVisitors1()->getId(),
            'datum' => date_format($this->matchesTable->getDate(), "d.m.Y"),
            'zapas_kontumace_domaci' => $this->matchesTable->getLossDefaultHome(),
            'zapas_kontumace_hoste' => $this->matchesTable->getLossDefaultVisitors(),
            'skrec_domaci' => $this->matchesTable->getRetireHome(),
            'skrec_hoste' => $this->matchesTable->getRetireVisitors(),
            'set1_domaci' => $this->matchesTable->getResults()->getSet1Home(),
            'set1_hoste' => $this->matchesTable->getResults()->getSet1Visitors(),
            'set2_domaci' => $this->matchesTable->getResults()->getSet2Home(),
            'set2_hoste' => $this->matchesTable->getResults()->getSet2Visitors(),
            'set3_domaci' => $this->matchesTable->getResults()->getSet3Home(),
            'set3_hoste' => $this->matchesTable->getResults()->getSet3Visitors(),
            'tb1_domaci' => $this->matchesTable->getResults()->getTb1Home(),
            'tb1_hoste' => $this->matchesTable->getResults()->getTb1Visitors(),
            'tb2_domaci' => $this->matchesTable->getResults()->getTb2Home(),
            'tb2_hoste' => $this->matchesTable->getResults()->getTb2Visitors(),
            'tb3_domaci' => $this->matchesTable->getResults()->getTb3Home(),
            'tb3_hoste' => $this->matchesTable->getResults()->getTb3Visitors(),
            'zapas_muzi_poradi' => $this->matchesTable->getMatchMenOrder(),
            'zapas_info' => $this->matchesTable->getDescriptions()
        ]);
    }

    public function update($form) { //provede se po odeslání vyplněného formuláře pro úpravu hráče
        try {
            $idMatch = $this->presenter->getParameter('id');
            $values = $form->getValues();
            $this->matchesTable->updateMatchesTable($idMatch, $this->setsHome, $this->setsVisitors, $this->winHome, $this->winVisitors, $values);
            $this->matchesTable->setResult($this->result);
            $this->matchesTable->logUpdate();
        } catch (\Nette\Database\DriverException $e) {
            //$form->addError('Chyba při úpravě registrace. Zadaný hráč byl je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět.');
            //$this->presenter->flashMessage('Chyba při vkládání zápasu. Zápas s danými parametry nelze vložit, zkontrolujte správnost všech údajů. Není již uvedený zápas jednou vložen? Splňuje všechny náležitosti - maximální počet zápasů daného typu v utkání, apod? Změny vráceny zpět.', 'error');
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            $this->presenter->redirect('Sport:zapasyUprava', $this->presenter->getParameter('id'));
        } catch (\Nette\Neon\Exception $e) {
            //$form->addError('Chyba při úpravě registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->mailer->setMessage('Chyba - úprava zápasu dvouhry', 'Nastala nespecifikovaná chyba při editaci zápasu dvouhry - úpravě. Jednalo se o zápas s id ' . $this->presenter->getParameter('id') . ' mezi hráči ' . $form['hrac1_domaci']->getValue() . 'a ' . $form['hrac1_hoste']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při úpravě zápasu. Nespecifikovaný typ, kontaktujte správce.', 'error');
            $this->presenter->redirect('Sport:zapasyUprava', $this->presenter->getParameter('id'));
        }
    }

    public function insert($form) { //provede se po odeslání vyplněného formuláře pro vložení nového hráče
        try {
            //bdump($form->getValues());
            $idPlay = $this->presenter->getParameter('idPlay');
            $matchType = $this->presenter->getAction() == 'zapasyNovyDvouhraMuzi' ? 1 : 3;
            $values = $form->getValues();
            $this->matchesTable->insertMatchesTable($idPlay, $matchType, $this->setsHome, $this->setsVisitors, $this->winHome, $this->winVisitors, $values);
            $this->matchesTable->setResult($this->result);
            $this->matchesTable->logInsert();
            //$values = $form->getValues();
            //$this->playsTable->insertMatchesTable($values);
        } catch (\Nette\Database\DriverException $e) {
            $form->addError(NULL);
            //$form->addError('Chyba při vkládání registrace. Zadaný hráč - ' . $form['id_hrac']->getSelectedItem() . ' - je již ve zvoleném období v nějakém klubu zaregistrován. Změny vráceny zpět');
            $this->presenter->flashMessage($this->getDatabaseErrorText($e), 'error'); // výpis chyby přímo z triggerové funkce z databáze
            //$this->presenter->flashMessage('Chyba při vkládání zápasu. Zápas s danými parametry nelze vložit, zkontrolujte správnost všech údajů. Není již uvedený zápas jednou vložen? Splňuje všechny náležitosti - maximální počet zápasů daného typu v utkání, apod? Změny vráceny zpět.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        } catch (\Nette\Neon\Exception $e) {
            $form->addError(NULL);
//$form->addError('Chyba při vkládání registrace. Nespecifikovaný typ, kontaktujte správce.');
            $this->mailer->setMessage('Chyba - vkládání zápasu dvouhry', 'Nastala nespecifikovaná chyba při editaci zápasu dvouhry - vkládání. Jednalo se o zápas mezi hráči ' . $form['hrac1_domaci']->getValue() . 'a ' . $form['hrac1_hoste']->getValue() . '. Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();
            
            $this->presenter->flashMessage('Chyba při vkládání zápasu. Nespecifikovaný typ, kontaktujte správce.', 'error');
            //$this->presenter->redirect('Sport:registraceNova');
        }
    }

}
