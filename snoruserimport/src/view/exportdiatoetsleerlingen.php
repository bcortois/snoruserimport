<?php
$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

session_start();
$report = $_SESSION['report_obj'];


foreach ($report as $wisaStudent) {
    $gender = null;
    if($wisaStudent->getGender() == 'V') {
        $gender = '1';
    }
    else {
        $gender = '0';
    }
    echo $wisaStudent->getFirstName() . ',' . $wisaStudent->getLastName()  . ',' . $wisaStudent->getBirthDate() . ',' . $gender . ',' . $wisaStudent->getClassName() . ',' . $wisaStudent->getWisaId() . '<br>';
}