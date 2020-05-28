<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 12-06-18
 * Time: 14:17
 */

namespace Snor\UserImport\Service;


class AdUsers
{
    /**
     * DEPRACTED
     * 26/05/2020: Deze code was al enige tijd niet meer in gebruik. Er is in SyncModule.php nog een functie aanwezig om deze code uit te voeren.
     * De parrams van de AdImport constuctor functie hieronder werden verwijderd omwille van security issues. Deze code staat hier louter informatief voor het geval er nog referenties gevonden worden.
     **/
    public function fetch() {
        $model = new \Snor\UserImport\Model\AdImport();
        $dn = 'OU=personen,OU=leerlingen,OU=duffel,DC=snor,DC=lok';
        $filter =  '(cn=*)';
        $attributes = array('userprincipalname', 'givenname', 'sn', 'givenname', 'displayname', 'mail', 'name', '1', 'telephonenumber', 'memberof', 'employeeid');

        $model->fetchAdUsers($dn,$filter,$attributes);
        return $model->getResult();
    }
}