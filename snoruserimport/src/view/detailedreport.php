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
    //print_r($report->getNotInAd());
    //$controller->CommitSync($report);


    //$controller = new \Snor\UserImport\Controller\SyncController();
    //$report = new \Snor\UserImport\Bll\Report();
    function getRequestParamValue($request) {
        if (isset($_GET[$request])) {
            return $_GET[$request];
        }
        else {
            return '';
        }
    }
    /*if(isset($_GET['sync_report'])) {
        if ($_GET['sync_report'] == 'sec') {
            $controller->StudentsSoToAd($report);
        }
        elseif ($_GET['sync_report'] == 'hbo5') {
            $controller->StudentsHboToAd($report);
        }
    }*/
    echo '<a href="sync.php?sync_report='.getRequestParamValue('sync_report').'">Voer gebrukers toe aan active directory</a>';
    echo '<a href="wamsync.php?sync_report='.getRequestParamValue('sync_report').'">wam syncer (test)</a>';
    switch(getRequestParamValue('sync_report')) {
        case 'sec' : if ($report->getNotInAdCount() > 0) {
            foreach ($report->getNotInAd() as $wisaUser) {
                echo 'Voornaam: '.$wisaUser->getFirstName().'<br>';
                echo 'Achternaam: '.$wisaUser->getLastName().'<br>';
                echo 'Klas: '.$wisaUser->getClassName().'<br>';
                echo '//<br>';
            }
        }
        break;
        case 'hbo5' : if ($report->getNotInAdCount() > 0) {
            foreach ($report->getNotInAd() as $wisaUser) {
                echo 'Voornaam: '.$wisaUser->getFirstName().'<br>';
                echo 'Achternaam: '.$wisaUser->getLastName().'<br>';
                echo 'Klas: '.$wisaUser->getClassName().'<br>';
                echo '//<br>';
            }
        }
        break;
    }
?>
