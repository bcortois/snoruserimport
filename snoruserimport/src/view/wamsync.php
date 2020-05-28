<?php
// deze header is enkel nodig wanneer je text output naar de browser (echo, print_r, ...) die in utf-8 geëncodeerd is.
header('Content-type: text/html; charset=utf-8');
$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

session_start();
$report = $_SESSION['report_obj'];



$configLoader = new \Snor\UserImport\Helpers\ConfigLoader('../../config/settings.toml');
$config = $configLoader->getConfigContent();
$controller = new \Snor\UserImport\Controller\SyncController();
$wamImport = new \Snor\UserImport\Model\WamImport($config);




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
$emailAddress = $firstNameWithoutSpaces.'.'.$lastNameWithoutSpaces.'@student.snorduffel.be';
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
    $emailAddress = $username.'@student.snorduffel.be';
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

$wamUsers = array();
$usernameList = Array();
foreach ($report->getNotInAd() as $wisaStudent) {
    $salt = null;
    $username = createUsername($wisaStudent, $salt);
    while ($controller->adUserExist($username)) {
        echo 'jeeeeeep';
        $salt++;
        $username = createUsername($wisaStudent,$salt);
    }
    while (in_array($username,$usernameList)) {
        $salt++;
        $username = createUsername($wisaStudent,$salt);
    }
    $usernameList[] = $username;

    # temp var for displayname
    $displayName = $wisaStudent->getLastName() . '_' . $wisaStudent->getFirstName() . '_(' . $username . ')';

    //if ($wisaStudent->getEstablishmentCode() == 'Station6') {// && fnmatch("7*", $wisaStudent->getClassCode())) {
        echo $wisaStudent->getWisaId() . ',' . $wisaStudent->getFirstName() . ',' . $wisaStudent->getLastName() . ',' . $username . ',' . $displayName . '<br>'; // .',' . mb_detect_encoding($username) . '<br>';
    //}
    $adGroups = array();
    if ($wisaStudent->getEstablishmentCode() == 'Station6') {
        $adGroups = array('CN=Leerlingen,OU=groepen,OU=leerlingen,OU=duffel,DC=snor,DC=lok',
            'CN=leerlingen_SO,OU=groepen,OU=leerlingen,OU=duffel,DC=snor,DC=lok',
            'CN=wifi_leerlingen,OU=securitygroups,OU=duffel,DC=snor,DC=lok');
    }
    if ($wisaStudent->getEstablishmentCode() == 'Rooien23') {
        $adGroups = array('CN=Leerlingen,OU=groepen,OU=leerlingen,OU=duffel,DC=snor,DC=lok',
            'CN=studenten_HBO5,OU=groepen,OU=leerlingen,OU=duffel,DC=snor,DC=lok',
            'CN=wifi_studenten,OU=securitygroups,OU=duffel,DC=snor,DC=lok');
    }

    if ($_GET['sync_report'])
        $wamUser = new \Snor\UserImport\Bll\WamUser();
        $wamUser->setAdministrativeId($wisaStudent->getWisaId());
        $wamUser->setDepartment($wisaStudent->getClassCode());
        $wamUser->setChangePasswordAtLogon(true);
        $wamUser->setFirstName($wisaStudent->getFirstName());
        $wamUser->setDisplayName($wisaStudent->getLastName() . '_' . $wisaStudent->getFirstName() . '_(' . $username . ')');
        $wamUser->setEmailAddress($username . '@student.snorduffel.be');
        $wamUser->setEnabled('true');
        $wamUser->setLastName($wisaStudent->getLastName());
        $wamUser->setPath('OU=test,OU=personen,OU=leerlingen,OU=duffel,DC=snor,DC=lok');
        $wamUser->setRole('leerling');
        $wamUser->setSamAccountName($username);
        $wamUser->setSchoolName('SNOR');
        $wamUser->setUserPrincipalName($username . '@student.snorduffel.be');

    //test
    //echo $wamUser->getAdministrativeId() . ',' . $wamUser->getFirstName() . ',' . $wamUser->getLastName() . ',' . $wamUser->getEmailAddress() . ',' . mb_detect_encoding($wamUser->getEmailAddress()).'<br>';
    $wamUsers[] = $wamUser;



}

print_r($wamImport->addUsers($wamUsers));
