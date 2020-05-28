<?php
    $loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
    set_time_limit(30);
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors',1);
    $controller = new \Snor\UserImport\Controller\SyncController();
    $report = new \Snor\UserImport\Bll\Report();
    function getRequestParamValue($request) {
        if (isset($_GET[$request])) {
            return $_GET[$request];
        }
        else {
            return '';
        }
    }
    if(isset($_GET['sync_report'])) {
        if ($_GET['sync_report'] == 'sec') {
            $controller->studentsToAd($report);
        }
        elseif ($_GET['sync_report'] == 'hbo5') {
            /**
             * DEPRACTED
             * 26/05/2020: Deze code was al enige tijd niet meer in gebruik. Er is in index.html nog een knop aanwezig om deze code uit te voeren, vandaar dat deze hier in commentaar staat.
             * De functie in commentaar werd ook verwijderd omwille van security issues. Deze elseif staat hier louter informatief voor het geval er nog referenties gevonden worden.
             **/
            // $controller->studentsHboToAd($report);
        }
        elseif($_GET['sync_report'] == 'diatoets') {
            /**
             * DEPRACTED
             * 26/05/2020: Deze code was al enige tijd niet meer in gebruik. Er is in index.html nog een knop aanwezig om deze code uit te voeren, vandaar dat deze hier in commentaar staat.
             * De functie in commentaar werd ook verwijderd omwille van security issues. Deze elseif stata hier louter informatief voor het geval er nog referenties gevonden worden.
             **/
            // Moet aangepast worden zodat er een andere variabel dan $report gebruikt wordt. Het gebruik van deze variabel zorgt namelijk voor verwarring aangezien het in dit geval niet gaat om ene obj van de klasse Report.
            //$report = $controller->studentsSo();
        }
    }
    session_start();
    $_SESSION['report_obj'] = $report;
    $_SESSION['controller_obj'] = $controller;
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rapport</title>
</head>
<body>
    <table>
        <tr>
            <td>
                Sec - Aantal overeenkomsten (wisa<->AD):
            </td>
            <td>
                <?php
                    if (getRequestParamValue('sync_report') != 'diatoets') {
                        if ($report->getMatchCount() > 0) {
                            echo '<a href="matcher.php?sync_report=' . getRequestParamValue('sync_report') . '">' . $report->getMatchCount() . '</a>';
                        }
                    }
                ?>
                <?php //foreach($report->getMatches() as $match) { if (in_array('getEmailAddress',$match->getUpdates())){ print_r($match); }  }; ?>
            </td>
        </tr>
        <tr>
            <td>
                Sec - Aantal niet gesynchroniseerd (wisa<->AD):
            </td>
            <td>
                <?php
                    if (getRequestParamValue('sync_report') != 'diatoets') {
                        if ($report->getNotInAdCount() > 0) {
                            echo '<a href="detailedreport.php?sync_report=' . getRequestParamValue('sync_report') . '">' . $report->getNotInAdCount() . '</a>';
                        }
                    }
                ?>
            </td>
        </tr>
        <tr>
            <td>
                Sec - Aantal dat geen school loopt (AD<->wisa):
            </td>
            <td>
                <?php
                if (getRequestParamValue('sync_report') != 'diatoets') {
                    if ($report->getUsersNotAttendingSchool() > 0) {
                        echo '<a href="remove.php?sync_report=' . getRequestParamValue('sync_report') . '">' . count($report->getUsersNotAttendingSchool()) . '</a>';
                    }
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>
                Export voor diatoets:
            </td>
            <td>
                <?php
                    if (getRequestParamValue('sync_report') == 'diatoets') {
                        if (count($report) > 0) {
                            echo '<a href="exportdiatoetsleerlingen.php?sync_report=' . getRequestParamValue('sync_report') . '">' . count($report) . '</a>';
                        }
                    }
                ?>
            </td>
        </tr>
    </table>
</body>
</html>