<?php
// abstraktní třída pro běžné formuláře pro úpravy deatabáze kamer, uživatelů, apod., z této vychází konkrétní třídy pro jednotlivé sekce
namespace App\Form\BasicFormService;

abstract class BasicForms// tzv. továrna pro zpracování formulářů v sekci úpravy a vkládání registrací
{  
    protected $object;
    protected $presenter;
    protected $operation;
    
    protected abstract function start(); // abstraktní metoda pro inicializaci proměnných pro předávání dat při update do formulářů

    protected abstract function create($operation, $presenter, $object = null); // základní metoda s parametry pro obslužnou metodu formuláře (update, insert)
    
    protected abstract function setDefaults($form);
    
    protected abstract function validateForm($form);
    
    protected function getDatabaseErrorText($message) // metoda pro vrácení zájmové části textu exception z databáze
    {
        return substr($message, strpos($message, 'ERROR')+6, strpos($message, 'CONTEXT') - (strpos($message, 'ERROR')+6));
    }
    
    protected function validArray($values) // převede hodnoty zadané v odeslaném formuláři z asoc. pole do standardního a prvky s prázdným textem nahradí hodnotou null
    {
        $final = array();
        foreach ($values as $key => $value) {
            
            if ($value == '') {
                array_push($final, null);
            } else {
                array_push($final, $value);
            }
            
        }
        
        return $final;
    }

    protected abstract function update($form); //provede se po odeslání vyplněného formuláře pro úpravu entity
    
    protected abstract function insert($form); //provede se po odeslání vyplněného formuláře pro vložení nové entity 
}