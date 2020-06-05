<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 25-06-19
 * Time: 16:14
 */

namespace Snor\UserImport\Model;

class WamImport
{
    private $dataStore;
    private $result;
    private $config;

    public function __construct($config)
    {
        $this->result = Array();
        $this->config = $config;
        $this->dataStore = new \Snor\UserImport\Dal\WamConnector($this->config);
    }

    public function getResult()
    {
        return $this->result;
    }


    private function sanitizeString($value) {
        /**
         * Deze functie werd aangepast op 27-08-2019 om een bug te fixen. Vóór laatst genoemde aanpassing toegepast werd, converteerde de functie in alle gevallen de var $value naar utf-8 a.d.h.v. de utf8_encode functie.
         * Reden voor de functie: Om data die speciale ASCII karakters kan bevatten klaar te maken voor convertie naar JSON a.d.h.v. json_encode.
         * Dit bleek na tijdrovend debug werk een probleem.
         * Probleem: Wanneer je data dat al UTF-8 geëncodeerd is, converteerd met utf8_encode, dan zullen speciale karakters onleesbaar worden.
         * Aanpassing 27-08-2019: Om de bug te fixen werd een if structuur gebouwd.
         * deze check test of de input al dan niet UTF-8 geëncodeerd is. Als dat NIET het geval is dan zal er een convertie naar UTF-8 plaats vinden.
         * Bron: https://github.com/jdorn/php-reports/issues/100
         * Door deze aanpassing werkt de json_encode functie met de return data van deze functie en kan er via de WamConnector klasse een verzending via HTTP gebeuren zonder gegevensvervuiling.
         * Aanpassing 05-06-2020: Om ondersteuning te geven aan arrays, moest de functie aangepast worden. Vóór deze aanpassing werd er enkel ene actie uitgevoerd op value's van het type string.
         * Om strings die opgeslagen zijn in arrays te ondersteunen, moest er if toegevoegd wordne dat nakijkt of het om een array gaat. Indien dat het geval is, dan wordt de array geitereerd en
         * de functie recursief uitgevoerd op elk item dat zich voor doet.
        */
        if(gettype($value) === 'array') {
            $array = array();
            for ($i = 0; $i < count($value); $i++) {
                $array[] = $this->sanitizeString($value[$i]);
            }
            return $array;
        }
        if(gettype($value) === 'string') {
            if ( false === mb_check_encoding($value, 'UTF-8') ) return $value = utf8_encode($value);
            else return $value;
        }
    }

    public function addUsers($wamUsers) {
        $dataset = array();
        foreach ($wamUsers as $wamUser) {
            $postStrings = array();
            foreach($this->config['sync_instellingen']['wam_user_mapping'] as $methodName => $wamPostString) {
                $postStrings[$wamPostString] = $this->sanitizeString($wamUser->callGetMethodByName($methodName));
                //$postStrings[$wamPostString] = $wamUser->callGetMethodByName($methodName);
            }
            $dataset[] = $postStrings;
        }
        return $this->dataStore->addUsers($dataset);
    }

    public function addUsers2() {
        // bedoeld als test. Wordt niet meer onderhouden.
        return $this->dataStore->addUsers2();
    }

    public function updateUsers($wamUsers) {
        $dataset = array();
        foreach ($wamUsers as $wamUser) {
            $postStrings = array();
            foreach($this->config['sync_instellingen']['wam_user_mapping'] as $methodName => $wamPostString) {
                $postStrings[$wamPostString] = $this->sanitizeString($wamUser->callGetMethodByName($methodName));
                //$postStrings[$wamPostString] = $wamUser->callGetMethodByName($methodName);
            }
            $dataset[] = $postStrings;
        }
        return $this->dataStore->updateUsers($dataset);
    }
}