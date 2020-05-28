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

$wamUsers = array();
foreach ($report->getMatches() as $match) {
    if ($match->hasMultipleCandidates()) {
        echo $match->getReferenceObj()->getWisaId();
    }
    else {
        if ($match->getReferenceObj()->getWisaId() != $match->getDifferenceObj()->getAdministrativeId()) {
            echo '<br>';
            echo "Id ref: " . $match->getReferenceObj()->getWisaId() . " Id dif: " . $match->getDifferenceObj()->getAdministrativeId();

            $wamUser = new \Snor\UserImport\Bll\WamUser();
            $wamUser->setAdministrativeId($match->getReferenceObj()->getWisaId());
            $wamUser->setUserPrincipalName($match->getDifferenceObj()->getUserPrincipalName());
            $wamUsers[] = $wamUser;
        }
    }
}

print_r($wamImport->updateUsers($wamUsers));