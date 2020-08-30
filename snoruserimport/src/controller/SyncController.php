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

        $adSettings = $this->config['active_directory'];
        $adFilter = $this->config['active_directory']['filter'];
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
        // Om de verwijder-functie goed te laten werken moet je de $adConnectorInfo aanpassen.

        $adConnectorInfo = new \Snor\UserImport\Dal\AdConnectorInfo();
        $adConnectorInfo->setSearchScope(
            '',
            '(cn=*)',
            array('userprincipalname','givenname','sn','givenname','displayname','mail','name','1','telephonenumber','memberof','employeeid'));

        $wisaConnectorInfo = new \Snor\UserImport\Dal\WisaConnectorInfo();

        $syncEngine = new \Snor\UserImport\Model\SyncEngine($adConnectorInfo,$wisaConnectorInfo);
        $syncEngine->wisaToAd($report);
    }

    public function adUserExist($samAccountName) {
        $adSettings = $this->config['active_directory'];
        $adFilter = $this->config['active_directory']['filter'];
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

    public function commitSync($report) {

        $wamImport = new \Snor\UserImport\Model\WamImport($this->config);


        function getBirthDay($date) {
            return substr($date,8,2);
        }

        function getBirthMonth($date) {
            return substr($date,5,2);
        }


        // onderstaande code is voor personeelsleden, waar het mailadres gemaakt moet worden a.d.h.v. de voor- en achternaam.
        // De code maakt een mail adres van deze 2 velden en checkt of het een valid mail adres is.
        // als dat niet het geval is blijft het mailadres veld leeg.
                /*$firstNameWithoutSpaces = str_replace(' ', '', $match->getReferenceObj()->getFirstName());
                $lastNameWithoutSpaces = str_replace(' ', '', $match->getReferenceObj()->getLastName());
                $emailAddress = $firstNameWithoutSpaces.'.'.$lastNameWithoutSpaces.'@school.be';
                if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    $emailAddressToLowerCase = strtolower($emailAddress);
                    if($match->getDifferenceObj()->getEmailAddress() != $emailAddressToLowerCase) {
                        $match->getDifferenceObj()->setEmailAddress($emailAddressToLowerCase);
                        // We slagen de naam van de getmethod op in een array van het object report.
                        // Zo kunnen we later deze gegevens overlopen om zo de waarde van de properties die aangepast zijn na de sync, gemakkelijk terug te vinden.
                        $match->addUpdate('getEmailAddress');
                    }
                }*/

        // Deze functie houdt tijdens het aanmaken van een e-mailadres voor studenten rekening met speciale karakters in de voor-en achternaam en zorgt ervoor dat deze niet in het mailadres voorkomen.
        // NOTE 28/08: Deze lijkt niet nodig aangezien je a.d.h.v. de username het emailadres kan (moet) opmaken (In geval van werking snor).
        function createEmailAddress($username) {
            $emailAddress = $username.'@school.be';
            $emailAddressToLowerCase = strtolower($emailAddress);
            //$options = array('flags' => FILTER_FLAG_EMAIL_UNICODE);
            return filter_var($emailAddressToLowerCase, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
        }

        function replaceSpecialChars ($string) {
            $replacements = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            return strtr($string,$replacements );
        }

        function createUsername($wisaStudent,$salt) {
            $firstName = $wisaStudent->getFirstName();
            $lastName = $wisaStudent->getLastName();
            $username = "$firstName.$lastName";
            if ($salt) {
                // De var salt bepaalt of er een cijfer achter de gebruiksnaam moet komen om dubbels te voorkomen.
                // Indien $salt een waarde werd toegekend, dan zal deze waarde nogmaals vehoogt worden met 1 zodat de gebruikersnaam van de eerste dubbel begint met 2 i.p.v. 1.
                // naarmate er meer dubbels wordne ontdekt voor dezelfde gebruikernaam, blijft de salt var verhogen.
                $salt++;
                $username = $username . $salt;
            }
            // mb_strtolower houdt rekening met encoding zodat ook karakters met accenten naar lowercase geconverteerd kunnen worden.
            $username = mb_strtolower($username, 'UTF-8');

            //de replace functie zoekt karakter met een accent uit de gebruikersnaam en vervangt ze met hetzelfde karakter zonder accent.
            return replaceSpecialChars($username);
        }

        $syncSettingsStudent = $this->config['sync_instellingen']['leerling'];
        $wamUsers = array();
        $usernameList = Array();
        foreach ($report->getNotInAd() as $wisaStudent) {
            $salt = null;
            $username = createUsername($wisaStudent, $salt);
            while ($this->adUserExist($username)) {
                $salt++;
                $username = createUsername($wisaStudent,$salt);
            }
            while (in_array($username,$usernameList)) {
                $salt++;
                $username = createUsername($wisaStudent,$salt);
            }
            $usernameList[] = $username;
            $primarySmtp = $username . '@' . $syncSettingsStudent['domainname'];

            if ($_GET['sync_report'])
                $wamUser = new \Snor\UserImport\Bll\WamUser();
            $wamUser->setAdministrativeId($wisaStudent->getWisaId());
            $wamUser->setDepartment($wisaStudent->getClassCode());
            if ($syncSettingsStudent['department'] != 'informat') {
                # Als in de config het veld 'department' niet op 'informat' staat, dan geeft dat aan dat deze wamUser property ingevuld moet worden met de waarde uit de config.
                $wamUser->setDepartment($syncSettingsStudent['department']);
            }
            $wamUser->setChangePasswordAtLogon((bool) $syncSettingsStudent['change_password_at_logon']);
            $wamUser->setFirstName($wisaStudent->getFirstName());
            $wamUser->setDisplayName($wisaStudent->getFirstName().' '.$wisaStudent->getLastName().' | '.$syncSettingsStudent['role'].' '.$syncSettingsStudent['school_name']);
            $wamUser->setEmailAddress($primarySmtp);
            $wamUser->setEnabled($syncSettingsStudent['enable_account']);
            $wamUser->setLastName($wisaStudent->getLastName());

            // Bugfix 28-08-2020: Het kan voorvallen dat de data uit Informat een niet voorspelbare vestigingscode heeft, het gaat dan meetal om een menselijke fout.
            // Deze anomaliteiten gaf problemen voor onderstaande code omdat deze enkel rekening hield met de vestigingscodes die beschreven staan in de config.
            // Als fix werd er een "default" waarde toegevoegd in de config voor alle vestigingscodes die niet in de config staan beschreven.
            // hieronder werd de code aangepast om te werken met deze default of fallback waarde.
            $pathRecognized = false;
            foreach ($syncSettingsStudent['ou_paths'] as $ouPath) {
                if ($ouPath['vestigingscode'] === $wisaStudent->getEstablishmentCode()) {
                    $wamUser->setPath($ouPath['path']);
                    $pathRecognized = true;
                }
            }
            if (!$pathRecognized) {
                $wamUser->setPath($syncSettingsStudent['ou_paths_default']['path']);
            }

            $wamUser->setRole($syncSettingsStudent['role']);
            $wamUser->setSamAccountName($username);
            $wamUser->setSchoolName($syncSettingsStudent['school_name']);
            $wamUser->setUserPrincipalName($primarySmtp);

            $availableGroups = $syncSettingsStudent['ad_groepen'];
            foreach ($availableGroups as $groupObj) {
                if ($wisaStudent->getEstablishmentCode() == $groupObj['vestigingscode']) {
                    $wamUser->addGroupMembership($groupObj['group_dn']);
                }
            }
            //test
            //echo $wamUser->getAdministrativeId() . ',' . $wamUser->getFirstName() . ',' . $wamUser->getLastName() . ',' . $wamUser->getEmailAddress() . ',' . mb_detect_encoding($wamUser->getEmailAddress()).'<br>';
            echo $wamUser->getAdministrativeId() . ',' . $wamUser->getFirstName() . ',' . $wamUser->getLastName() . ',' . $username . ',' . $wamUser->getDisplayName() . '<br>'; // .',' . mb_detect_encoding($username) . '<br>';

            $wamUsers[] = $wamUser;



        }

        return $wamImport->addUsers($wamUsers);
    }
}