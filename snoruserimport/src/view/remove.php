<?php
$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
header('Content-type: text/html; charset=utf-8');

session_start();
$report = $_SESSION['report_obj'];
$controller = new \Snor\UserImport\Controller\SyncController();



$usernameList = Array();
foreach ($report->getUsersNotAttendingSchool() as $adUser) {
    echo $adUser->getAdministrativeId() . ',' . $adUser->getFirstName() . ',' . $adUser->getLastName() . ',' . $adUser->getUserPrincipalName() . '<br>';
}
//print_r($report->getMatches());