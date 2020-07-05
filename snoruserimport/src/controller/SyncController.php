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
            '',
            '(cn=*)',
            array('userprincipalname','givenname','sn','givenname','displayname','mail','name','1','telephonenumber','memberof','employeeid'));

        $wisaConnectorInfo = new \Snor\UserImport\Dal\WisaConnectorInfo();

        $syncEngine = new \Snor\UserImport\Model\SyncEngine($adConnectorInfo,$wisaConnectorInfo);
        $syncEngine->wisaToAd($report);
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
            // onderstaande code slaagt de eerste letter op van de voornaam en de achternaam, door gebruik van mb_str zijn multibyte chars ondersteund en is het mogelijke een encoding te specifiëren zodat
            // namen met accenten ook werken door utf-8 als encoding op te geven.
            $charF = mb_substr($wisaStudent->getFirstName(),0,1, 'UTF-8');
            $charL = mb_substr($wisaStudent->getLastName(),0,1, 'UTF-8');
            // onderstaande code haalt de dag van de maand en de maand uit de datum van het Student obj.
            $birthDay = getBirthDay($wisaStudent->getBirthDate());
            $birthMonth = getBirthMonth($wisaStudent->getBirthDate());
            if ($salt) {
                //als $salt de waarde true bevat dan zal de nummer van de maand verhoogt worden met 1
                $birthMonth+=$salt;
            }
            //onderstaande code lijmt de hierboven gevormde strings aan elkaar.
            //De functie strtolower zorgt ervoor dat er geen hoofdletter aanwezig zijn.
            //De str_pad zorgt ervoor dat, in geval de dag of de maand maar uit 1 cijfer bestaand, er een nul voor wordt geplaatst. (bv. 09 ipv 9)
            // mb_strtolower houdt rekening met encoding zodat ook karakters met accenten naar lowercase geconverteerd kunnen worden.
            $username = mb_strtolower($charL.$charF.str_pad($birthDay,2,'0',STR_PAD_LEFT).str_pad($birthMonth,2,'0',STR_PAD_LEFT), 'UTF-8');
            // met de iconv functie worden speciale accenten van de voor-/achternaam uit de gebruikernaam gehaald.
            //return preg_replace("/&([a-z])[a-z]+;/i", "$1", $username);
            //return slugify($username);
            //return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $username);

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
            # temp var for displayname
            $displayName = $wisaStudent->getLastName() . '_' . $wisaStudent->getFirstName() . '_(' . $username . ')';

            //if ($wisaStudent->getEstablishmentCode() == 'Station6') {// && fnmatch("7*", $wisaStudent->getClassCode())) {
            echo $wisaStudent->getWisaId() . ',' . $wisaStudent->getFirstName() . ',' . $wisaStudent->getLastName() . ',' . $username . ',' . $displayName . '<br>'; // .',' . mb_detect_encoding($username) . '<br>';
            //}


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
            $wamUser->setDisplayName($wisaStudent->getLastName() . '_' . $wisaStudent->getFirstName() . '_(' . $username . ')');
            $wamUser->setEmailAddress($primarySmtp);
            $wamUser->setEnabled($syncSettingsStudent['enable_account']);
            $wamUser->setLastName($wisaStudent->getLastName());
            foreach ($syncSettingsStudent['ou_paths'] as $ouPath) {
                if ($ouPath['vestigingscode'] === $wisaStudent->getEstablishmentCode()) {
                    $wamUser->setPath($ouPath['path']);
                }
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
            $wamUsers[] = $wamUser;



        }

        return $wamImport->addUsers($wamUsers);
    }
}