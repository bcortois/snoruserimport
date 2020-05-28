<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 13-06-18
 * Time: 12:59
 */

namespace Snor\UserImport\Controller;


class SyncController
{
    //private $syncEngine;
    private $config;
    /**
     * SyncController constructor.
     * @param $syncEngine
     */
    public function __construct()
    {
        //$this->syncEngine = new \Snor\UserImport\Model\SyncEngine();
        $configLoader = new \Snor\UserImport\Helpers\ConfigLoader('../../config/settings.toml');
        $this->config = $configLoader->getConfigContent();
    }

    public function studentsToAd($report) {
        // Om de verwijder-functie goed te laten werken moet je de $adConnectorInfo aanpassen naar 'OU=so,OU=personen,OU=leerlingen,OU=duffel,DC=snor,DC=lok'.

        $adSettings = $this->config['active_directory']['snor'];
        $adFilter = $this->config['active_directory']['snor']['filter'];
        $adConnectorInfo = new \Snor\UserImport\Dal\AdConnectorInfo($adSettings['dc'],$adSettings['user_dn'],$adSettings['wachtwoord']);
        $adConnectorInfo->setSearchScope(
            $adFilter['leerlingen_base_dn'],
            '(cn=*)',
            $adFilter['object_attributen']
        );

        $informatSettings = $this->config['informat'];
        $webserviceUri = $informatSettings['base_uri'].$informatSettings['resource_uri']['get_students'];
        $apiConnectorInfo = array();
        if (empty($informatSettings['referentie_datum'])) {
            $informatSettings['referentie_datum'] = date("d-m-Y", time());
        }
        echo $informatSettings['referentie_datum'];
        foreach ($informatSettings['instellingsnummer'] as $institutionNumber) {
            $apiConnectorInfo[] = new \Snor\UserImport\Dal\InformatConnectorInfo(
                $webserviceUri,
                $informatSettings['gebruiker'],
                $informatSettings['wachtwoord'],
                $informatSettings['schooljaar'],
                $institutionNumber,
                $informatSettings['referentie_datum'],
                $informatSettings['hoofdstructuur']
            );
        }
        $syncEngine = new \Snor\UserImport\Model\SyncEngine($adConnectorInfo,$apiConnectorInfo,$this->config);
        $syncEngine->informatToAd($report);
    }

    public function studentsHboToAd($report) {
        /**
         * DEPRACTED
         * 26/05/2020: Deze code was al enige tijd niet meer in gebruik.
         * De params van de AdConnectorInfo en WisaConnectorInfo constructor werden verwijderd omwille van security issues. Deze functie staat hier louter informatief voor het geval er nog referenties gevonden worden.
         **/
        // Om de verwijder-functie goed te laten werken moet je de $adConnectorInfo aanpassen naar 'OU=hbo5,OU=personen,OU=leerlingen,OU=duffel,DC=snor,DC=lok'.

        $adConnectorInfo = new \Snor\UserImport\Dal\AdConnectorInfo();
        $adConnectorInfo->setSearchScope(
            'OU=personen,OU=leerlingen,OU=duffel,DC=snor,DC=lok',
            '(cn=*)',
            array('userprincipalname','givenname','sn','givenname','displayname','mail','name','1','telephonenumber','memberof','employeeid'));

        $wisaConnectorInfo = new \Snor\UserImport\Dal\WisaConnectorInfo();

        $syncEngine = new \Snor\UserImport\Model\SyncEngine($adConnectorInfo,$wisaConnectorInfo);
        $syncEngine->wisaToAd($report);
    }

    public function commitSync($report) {

        //$this->syncEngine->SyncMatches($report);
    }

    public function adUserExist($samAccountName) {
        $adSettings = $this->config['active_directory']['snor'];
        $adFilter = $this->config['active_directory']['snor']['filter'];
        $adConnectorInfo = new \Snor\UserImport\Dal\AdConnectorInfo($adSettings['dc'],$adSettings['user_dn'],$adSettings['wachtwoord']);
        $adConnectorInfo->setSearchScope(
            $adFilter['leerlingen_base_dn'],
            '(cn=*)',
            $adFilter['object_attributen']
        );

        $syncEngine = new \Snor\UserImport\Model\SyncEngine($adConnectorInfo,null, $this->config);
        return $syncEngine->adUserExists($samAccountName, $adConnectorInfo);
    }

    public function addUser($adUser, $adGroups) {
        /**
         * DEPRACTED
         * 26/05/2020: Deze functie was al enige tijd niet meer in gebruik.
         * De params van de AdConnectorInfo constructor werden verwijderd omwille van security issues. Deze functie staat hier louter informatief voor het geval er nog referenties gevonden worden.
         **/
        $adConnectorInfo = new \Snor\UserImport\Dal\AdConnectorInfo();
        $syncEngine = new \Snor\UserImport\Model\SyncEngine($adConnectorInfo,null);
        if ($syncEngine->addUser($adUser,$adConnectorInfo)) {
            foreach ($adGroups as $adGroup) {
                $syncEngine->addToGroup($adUser,$adGroup,$adConnectorInfo);
            }
        }
    }

    /**
     *
     * DEPRACTED
     * 26/05/2020: Deze code was al enige tijd niet meer in gebruik. Er is in rapport.php nog een verwijzing aanwezig, maar die staat ook als deprecated in commentaar.
     * De param's van de WisaConnectorInfo() constructor in commentaar werden ook verwijderd omwille van security issues. De functie hieronder staat hier louter informatief voor het geval er nog referenties gevonden worden.
     *

    // function moet verhuizen naar een nieuwe klasse (bv. ImportController)
        public function studentsSo()
        {
            $wisaConnectorInfo = new \Snor\UserImport\Dal\WisaConnectorInfo();
            $wisaImport = new \Snor\UserImport\Model\WisaImport($wisaConnectorInfo);
            $wisaImport->fetchStudents(false);

            return $wisaImport->getResult();
        }
    */
}