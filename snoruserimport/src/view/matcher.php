<?php
header('Content-type: text/html; charset=utf-8');
$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
function getRequestParamValue($request) {
    if (isset($_GET[$request])) {
        return $_GET[$request];
    }
    else {
        return '';
    }
}
session_start();
$report = $_SESSION['report_obj'];
$controller = $_SESSION['controller_obj'];
echo '<a href="wamsync-changes.php">wam syncer - sync informat ID (test)</a>';
foreach ($report->getMatches() as $match) {
    if($match->hasMultipleCandidates()) {
        $differenceObj = $match->getDifferenceObj();
        echo '<br><h2>MATCH</h2>';
        echo '<br><b>Reference</b><br>';
        echo 'id: ' . $match->getReferenceObj()->getWisaId() . ' -- ';
        echo 'firstname: ' . $match->getReferenceObj()->getFirstName() . ' -- ';
        echo 'lastname: ' . $match->getReferenceObj()->getLastName() . ' -- ';
        echo 'birthdate: ' . $match->getReferenceObj()->getBirthDate();
        echo '<br><b>Matches</b><br>';
        for ($i = 0; $i < count($differenceObj); $i++) {
            echo 'id: ' . $differenceObj[$i]->getAdministrativeId() . ' -- ';
            echo 'firstname: ' . $differenceObj[$i]->getFirstName() . ' -- ';
            echo 'lastname: ' . $differenceObj[$i]->getLastName() . ' -- ';
            echo 'upn: ' . $differenceObj[$i]->getUserPrincipalName() . '<br>';
        }
    }
}