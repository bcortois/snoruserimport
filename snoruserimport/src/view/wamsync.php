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

$controller = new \Snor\UserImport\Controller\SyncController();

print_r($controller->commitSync($report));
