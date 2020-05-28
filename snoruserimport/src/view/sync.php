<?php
$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

session_start();
$report = $_SESSION['report_obj'];
$controller = new \Snor\UserImport\Controller\SyncController();


function getBirthDay($date) {
    return substr($date,8,2);
}

function getBirthMonth($date) {
    return substr($date,5,2);
}

function createUsername($wisaStudent,$salt) {
    // onderstaande code slaagt de eerste letter op van de voornaam en de achternaam, door gebruik van mb_str zij multibyte chars ondersteund
    $charF = mb_substr($wisaStudent->getFirstName(),0,1);
    $charL = mb_substr($wisaStudent->getLastName(),0,1);
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
    return strtolower($charL.$charF.str_pad($birthDay,2,'0',STR_PAD_LEFT).str_pad($birthMonth,2,'0',STR_PAD_LEFT));
}

/*function downloadList($report,$controller) {
    $filename = "export.csv";
    $delimiter=";";
    $f = fopen('php://output', 'w');
    foreach ($report->getNotInAd() as $wisaStudent) {
        $salt = null;
        $username = createUsername($wisaStudent, $salt);
        while ($controller->adUserExist($username)) {
            $salt++;
            $username = createUsername($wisaStudent,$salt);
        }
        $newLine = Array($wisaStudent->getWisaId(),$wisaStudent->getFirstName(),$wisaStudent->getLastName(),$username);
        // generate csv lines from the inner arrays
        fputcsv($f, $newLine, $delimiter);

    }
    fclose($f);
    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    //fpassthru($f);
    readfile($f);
    die();
}*/

$usernameList = Array();
foreach ($report->getNotInAd() as $wisaStudent) {
    $salt = null;
    $username = createUsername($wisaStudent, $salt);
    while ($controller->adUserExist($username)) {
        $salt++;
        $username = createUsername($wisaStudent,$salt);
    }
    while (in_array($username,$usernameList)) {
        $salt++;
        $username = createUsername($wisaStudent,$salt);
    }
    $usernameList[] = $username;
    //if ($wisaStudent->getClassName()[0] == '7') {
        echo $wisaStudent->getWisaId() . ',' . $wisaStudent->getFirstName() . ',' . $wisaStudent->getLastName() . ',' . $username . '<br>';
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
        $adUser = new \Snor\UserImport\Bll\AdUser();
        $adUser->setAdministrativeId($wisaStudent->getWisaId());
        $adUser->setFirstName($wisaStudent->getFirstName());
        $adUser->setLastName($wisaStudent->getLastName());
        $adUser->setAdministrativeId($wisaStudent->getWisaId());
        $adUser->setOfficialAddress($wisaStudent->getOfficialAddress());
        $adUser->setUserPrincipalName($username . '@student.snorduffel.be');
    //}
    if($controller->addUser($adUser,$adGroups)) {
    }
    else {
    }
}



/*
echo $report->getNotInAdCount();

$attr = Array();
$attr['mail'][] = $matchArr[0][0]->getFirstName().'.'.$matchArr[0][0]->getLastName().'@student.snorduffel.be';
$attr['employeeid'] = $matchArr[0][0]->getWisaId();
$dn = $matchArr[0][1]['dn'];


if($adConn->modifyUser($dn,$attr)) {
    echo $dn;
}
