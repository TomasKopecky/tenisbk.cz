<?php

/**
 * Třída editačního presenteru uživatelů modulu Admin - přístupná pouze administrátorům
 * Správa uživatelské databáze
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
use App\Model\Entity\CmsEntity\ArticlesTableList;
use App\Module\ErrorMailer;

class CmsPresenter extends BasicPresenterLayout {

    /**
     * Předání závislosti formou inject pro továrnu ArticleForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \ArticleForm @inject
     */
    public $articleFormFactory;

    /**
     * Předání závislosti formou inject pro továrnu GalleryForm z config souboru
     * - zajistí automaticky vytvoření instance třídy
     *
     * @var \GalleryForm @inject
     */
    public $galleryFormFactory;
    private $articlesList,
            $mailer;

    public function __construct(ArticlesTableList $articlesList, ErrorMailer $mailer) {
        parent::__construct();
        $this->mailer = $mailer;
        $this->articlesList = $articlesList;
    }

    /**
     * Metoda pro zjištění identity přihlášeného uživatele a případné přesměrování
     *
     * @return void
     */
    public function startup() {
        parent::startup();
        $this->checkLogin();
        $this->checkRoles(array('Admin', 'Správce'));
    }

    public function renderClanky() {
        $this->articlesList->setArticlesPageLength(0);
        $this->articlesList->calcArticlesTableList(false);
        $this->template->articlesList = $this->articlesList->getArticlesTableList();
    }

    public function renderFotogalerie() {
        
    }

    public function renderClankyNovy() {
        $this->template->formName = "insertArticleForm";
    }

    public function renderFotogalerieNova() {
        $this->template->formName = "insertGalleryForm";
    }

    public function renderClankyUprava($id) {
        if (is_null($this->articlesList->getTitle())) {
            $this->articlesList->setId($id);
            $this->articlesList->calcArticlesTable();
        }
        if (empty($this->articlesList->getTitle())) {
            $this->presenter->flashMessage('Článek zadaného ID není v databázi', 'error');
            $this->presenter->redirect('Cms:clanky');
        }
        $this->template->formName = "updateArticleForm";
    }

    public function renderFotogalerieUprava($id) {
        $this->template->formName = "updateGalleryForm";
    }

    public function createComponentUpdateArticleForm() {
        $form = $this->articleFormFactory->create('update', $this, $this->articlesList); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Úprava článku s titulkem \'' . $form['titulek']->getValue() . '\' z roku ' . $form['rocnik']->getValue() . '\' provedena', 'info');
            $this->redirect('Cms:clanky');
        };
        return $form;
    }

    public function createComponentInsertArticleForm() {
        $form = $this->articleFormFactory->create('insert', $this); // metoda create z továrny RegForm pro update metodu
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Vložení článku s titulkem \'' . $form['titulek']->getValue() . '\' z roku ' . $form['rocnik']->getValue() . ' provedeno', 'info');
            $this->redirect('Cms:clanky');
        };
        return $form;
    }

    public function handleDeleteArticle($id) {
        $this->articlesList->setId($id);
        $this->articlesList->calcArticlesTable();
        try {
            $this->articlesList->deleteArticle();
            $this->articlesList->logDelete();
        }
        /* catch (\Nette\Database\DriverException $e) {
          $this->flashMessage('Chyba při mazání v článku v databázi s titulkem ' . $this->articlesList->getTitle() . '. Uživatel má již přiděleno oprávnění nebo má již historii v logu. Uživatele tedy pouze deaktivujte.', 'error');
          $this->redirect('uzivatele');
          } */ catch (\Exception $e) {
            $this->mailer->setMessage('Chyba - mazání článku', 'Nastala nespecifikovaná chyba při editaci článku - mazání. Jednalo se o článek s id ' . $this->articlesList->getId() . ', a titulkem "' . $this->articlesList->getTitle() . '". Text chyby: ' . $e->getMessage());
            $this->mailer->setSenderReceiver("error@tenisbk.cz", "admin@tenisbk.cz");
            $this->mailer->sendMessage();

            $this->flashMessage('Nezjištěná chyba při mazání článku s titulkem "' . $this->articlesList->getTitle() . '" - kontaktuje administrátora', 'error');
            $this->redirect('clanky');
        }
        $this->flashMessage('Vymazání článku s titulkem "' . $this->articlesList->getTitle() . '" úspěšně provedeno', 'info');
        $this->redirect('clanky');
    }

    public function handleDeleteGallery($id, $name, $roleName) {
        
    }

}
