<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 01-06-18
 * Time: 13:45
 */

namespace Snor\UserImport\Service;


class LoadStudents
{
    public function fetch() {
        /**
         * DEPRACTED
         * 26/05/2020: Deze code was al enige tijd niet meer in gebruik. Er is in SyncModule.php nog een functie aanwezig om deze code uit te voeren.
         * De parrams van de WisaImport constuctor functie hieronder werden leeg gemaakt omwille van security issues. Deze code staat hier louter informatief voor het geval er nog referenties gevonden worden.
         **/
        $model = new \Snor\UserImport\Model\WisaImport();

        $url = '';
        $username = '';
        $password = '';

        $model->fetchStudents($url,$username,$password);
        return $model->getResult();
    }
}