<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 12-06-18
 * Time: 15:32
 */

namespace Snor\UserImport\Controller;


class SyncModule
{
    public function wisaToAd($report) {
        $adService = new \Snor\UserImport\Service\AdUsers();
        $wisaService = new \Snor\UserImport\Service\LoadStudents();
        $wisaUsers = $wisaService->fetch();
        $adUsers = $adService->fetch();

        foreach ($wisaUsers as $a) {
            $skipArr = Array();
            $match = FALSE;
            for ($i = 0; $i < count($adUsers); $i++) {
                $wisaLastName = trim($a->getLastName());
                $wisaFirstName = trim($a->getFirstName());
                $adLastName = trim($adUsers[$i]->getLastName());
                $adFirstName = trim($adUsers[$i]->getFirstName());

                if (!(array_key_exists($i,$skipArr))) {
                    if ($wisaLastName == $adLastName) {
                        if ($wisaFirstName == $adFirstName) {
                            $report->match(array($a,$adUsers[$i]));
                            $match = TRUE;
                            break;
                        }
                        else {
                            //echo 'skipped'.$adUsers[$i]['sn'][0].'<br>';
                            $skipArr[]= $i;
                            continue;
                        }
                    }
                    else {
                    }
                    continue;
                }
            }
            // Geen indicatie, bevat dubbels.
            if(!$match) {
                $report->notInAd($a);
            }
        }


    }
}