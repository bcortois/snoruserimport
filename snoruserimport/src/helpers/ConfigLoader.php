<?php

/**
 * Created by PhpStorm.
 * User: cob
 * Date: 13-06-19
 * Time: 11:01
 *
 * Uitleg klasse:
 * Deze klasse laat je toe om schooleigen configuraties in te laden. Deze configuraties bevatten informaties over de connectie tot Informat en AD.
 * Ook andere zaken kunnen opgenomen worden in de configuratie. De klasse gebruik een parser om de gegevens uit het configbestand op te halen en in te laden als data types.
 * De code in de applicatie zorgt voor de afhandeling en gebruik van deze gegevens. Deze klassen zorgt enkel voor.
 * Momenteel kan deze configLoader werken met volgende configuratiebronnen:
 * - .toml bestand
 *
 * Gebruik:
 * Creëer een nieuw object van deze klassen en geef meteen het pad op naar een configuratie bestand. Afhankelijk van de omgeving waar de applicatie in
 * draait en waar de rootdoc ingesteld staat, kan je relatieve paden gebruiken om deze locatie aan te geven. Wanneer je een correct pad hebt opgegeven
 * zal de inhoud van het configuratiebestand in de vorm van een array in de property $configContent opgeslagen worden.
 * Je kan via de functie getConfigContent deze array opvragen.
 */

namespace Snor\UserImport\Helpers;

class ConfigLoader
{
    private $configContent;

    /**
     * ConfigLoader constructor.
     */
    public function __construct($configFilePath)
    {
        $this->importToml($configFilePath);
    }

    private function importToml($configFilePath) {
        $this->setConfigContent(\Toml\Parser::fromFile($configFilePath));
    }

    /**
     * @return mixed
     */
    public function getConfigContent()
    {
        return $this->configContent;
    }

    private function setConfigContent($array) {
        $this->configContent = $array;
    }
}