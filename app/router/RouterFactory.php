<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory {

    use Nette\StaticClass;

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter() {
        $router = new RouteList;
        $router[] = new Route('editace/clanky/new', 'Admin:Cms:clankyNovy');
        $router[] = new Route('editace/clanky/<id>', 'Admin:Cms:clankyUprava');
        $router[] = new Route('editace/systemy/new', 'Admin:Sport:systemyNovy');
        $router[] = new Route('editace/systemy/<id>', 'Admin:Sport:systemyUprava');
        $router[] = new Route('editace/souteze/new', 'Admin:Sport:soutezeNova');
        $router[] = new Route('editace/souteze/<id>', 'Admin:Sport:soutezeUprava');
        $router[] = new Route('editace/pusobeni/new', 'Admin:Sport:pusobeniNove');
        $router[] = new Route('editace/pusobeni/<id>', 'Admin:Sport:pusobeniUprava');
        $router[] = new Route('editace/utkani/<id>/zapasy', 'Admin:Sport:utkaniZapasy');
        $router[] = new Route('editace/zapasy/new/<idPlay>/ctyrhra-mix', 'Admin:Sport:zapasyNovyCtyrhraMix');
        $router[] = new Route('editace/zapasy/new/<idPlay>/ctyrhra-muzi', 'Admin:Sport:zapasyNovyCtyrhraMuzi');
        $router[] = new Route('editace/zapasy/new/<idPlay>/dvouhra-zeny', 'Admin:Sport:zapasyNovyDvouhraZeny');
        $router[] = new Route('editace/zapasy/new/<idPlay>/dvouhra-muzi', 'Admin:Sport:zapasyNovyDvouhraMuzi');
        $router[] = new Route('editace/zapasy/<id>', 'Admin:Sport:zapasyUprava');
        $router[] = new Route('editace/utkani/new', 'Admin:Sport:utkaniNove');
        $router[] = new Route('editace/utkani/<id>', 'Admin:Sport:utkaniUprava');
        $router[] = new Route('editace/kluby/new', 'Admin:Sport:klubyNovy');
        $router[] = new Route('editace/kluby/<id>', 'Admin:Sport:klubyUprava');
        $router[] = new Route('editace/registrace/new', 'Admin:Sport:registraceNova');
        $router[] = new Route('editace/registrace/<id>', 'Admin:Sport:registraceUprava');
        $router[] = new Route('editace/hraci/new', 'Admin:Sport:hraciNovy');
        $router[] = new Route('editace/hraci/<id>', 'Admin:Sport:hraciUprava');
        $router[] = new Route('editace/fotogalerie', 'Admin:Cms:fotogalerie');
        $router[] = new Route('editace/clanky', 'Admin:Cms:clanky');
        $router[] = new Route('editace/zapasy', 'Admin:Sport:zapasy');
        $router[] = new Route('editace/utkani', 'Admin:Sport:utkani');
        $router[] = new Route('editace/souteze', 'Admin:Sport:souteze');
        $router[] = new Route('editace/systemy', 'Admin:Sport:systemy');
        $router[] = new Route('editace/pusobeni', 'Admin:Sport:pusobeni');
        $router[] = new Route('editace/registrace', 'Admin:Sport:registrace');
        $router[] = new Route('editace/kluby', 'Admin:Sport:kluby');
        $router[] = new Route('editace/hraci', 'Admin:Sport:hraci');
        $router[] = new Route('uzivatele/opravneni/new', 'Admin:Uzivatele:opravneniNove');
        $router[] = new Route('uzivatele/opravneni/<id>', 'Admin:Uzivatele:opravneniUprava');
        $router[] = new Route('uzivatele/opravneni', 'Admin:Uzivatele:opravneni');
        $router[] = new Route('uzivatele/logy', 'Admin:Uzivatele:logy');
        $router[] = new Route('uzivatele/new', 'Admin:Uzivatele:uzivateleNovy');
        $router[] = new Route('uzivatele/<id>', 'Admin:Uzivatele:uzivateleUprava');
        $router[] = new Route('utkani/<id>', 'Web:Utkani:detail');
        $router[] = new Route('zapas/<id>', 'Web:Zapasy:detail');
        $router[] = new Route('klub/<slug>', 'Web:Kluby:detail');
        $router[] = new Route('hrac/<slug>', 'Web:Hraci:detail');
        $router[] = new Route('clanek/<slug>', 'Web:Clanky:detail');
        $router[] = new Route('registrace/<hash>', 'Web:SignUp:default');
        $router[] = new Route('editace', 'Admin:Sport:default');
        $router[] = new Route('uzivatele', 'Admin:Uzivatele:uzivatele');
        $router[] = new Route('kalendar', 'Web:Kalendar:default');
        $router[] = new Route('utkani', 'Web:Utkani:default');
        $router[] = new Route('vysledky', 'Web:Vysledky:default');
        $router[] = new Route('tabulka', 'Web:Tabulka:default');
        $router[] = new Route('kluby', 'Web:Kluby:default');
        $router[] = new Route('hraci', 'Web:Hraci:default');
        $router[] = new Route('zapasy', 'Web:Zapasy:default');
        $router[] = new Route('clanky', 'Web:Clanky:default');
        $router[] = new Route('zebricek', 'Web:Zebricek:default');
        $router[] = new Route('soupisky', 'Web:Soupisky:default');
        $router[] = new Route('pravidla', 'Web:Pravidla:default');
        $router[] = new Route('kontakty', 'Web:Kontakty:default');
        $router[] = new Route('<presenter>', 'Web:Homepage:default');
        return $router;
    }

}
