<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 12-06-18
 * Time: 10:20
 */

namespace Snor\UserImport\Model;


class AdImport
{
    private $dataStore;
    private $result;
    private $config;

    public function __construct($adConnectorInfo, $config)
    {
        $this->dataStore = new \Snor\UserImport\Dal\AdConnector($adConnectorInfo);
        $this->result = Array();
        $this->config = $config;
    }

    public function getResult()
    {
        return $this->result;
    }

    private function isInList($wisaId) {
        foreach ($this->result as $row) {
            if($row->getAdministrativeId() == $wisaId)
            {
                return TRUE;
            }
        }
    }

    public function fetchAdUsers() {
        if($this->dataStore->connect()) {
            $this->dataStore->fetchUsers();
            foreach ($this->dataStore->getData() as $row) {
                $adUser = new \Snor\UserImport\Bll\AdUser();

                /*
                 * UPDATE 14/06/2019:
                 * Met de introductie van een configbestand werd de functie aangepast zodat deze informatie uit het nadergenoemde configbestand haalt.
                 * Met deze info is de functie instaat een mapping te maken tussen de attributen van een aduser object en de properties van de BLL klasse AdUser in deze app.
                 * Er werd aan de klasse een functie toegevoegd die de juiste property uitzoekt door de naam uit de config te matchen met de property naam.
                 */
                foreach($this->config['sync_instellingen']['ad_user_mapping'] as $methodName => $adAttribute) {
                    if (isset($row[$adAttribute][0])) {
                        $adUser->callMethodByName($methodName, 'set', $this->sanitizeString($row[$adAttribute][0]));
                    }
                }

                // in commentaar geplaatst op 14/06/2019
                /*
                if (isset($row['employeeid'][0])) {
                    $adUser->setAdministrativeId($row['employeeid'][0]);
                }
                $adUser->setLastName($this->sanitizeString($row['sn'][0]));
                $adUser->setFirstName($this->sanitizeString($row['givenname'][0]));
                if (isset($row['mail'][0])) {
                    $adUser->setEmailAddress($row['mail'][0]);
                }
                $adUser->setUserPrincipalName($row['userprincipalname'][0]);
                // Wanneer een aduser al eens bijgewerkt/gematched is door de snoruserimport app dan wordt het attr 'info' ingevuld met de string 'synced'.
                // Hier wordt de attr getest op dit keyword. Als deze ingevuld is dan zal de property 'synced' van het object op true worden gezet.
                if (isset($row['info'][0]) && $row['info'][0] == 'synced') {
                    $adUser->setSynced(true);
                }
                if (isset($row['department'][0])) {
                    $adUser->setDepartment($row['department'][0]);
                }
                if (isset($row['physicalDeliveryOfficeName'][0])) {
                    $adUser->setDepartment($row['physicalDeliveryOfficeName'][0]);
                }
                if (isset($row['title'][0])) {
                    $adUser->setDepartment($row['title'][0]);
                }*/
                $this->result[] = $adUser;
            }
        }
        $this->dataStore->disconnect();
    }

    private function sanitizeString($string) {
        return utf8_encode($string);
    }

    public function samAccountNameExists($samAccountName) {
        $result = false;
        if($this->dataStore->connect()) {
            $result = $this->dataStore->samAccountNameExists($samAccountName);
        }
        $this->dataStore->disconnect();
        return $result;
    }
    public function userPrincipalNameExists($userPrincipalName) {
        $result = false;
        if($this->dataStore->connect()) {
            $result = $this->dataStore->userPrincipalNameExists($userPrincipalName);
        }
        $this->dataStore->disconnect();
        return $result;
    }

    public function addUser($adUser) {
        $success = false;
        if($this->dataStore->connect()) {
            $i = strrpos($adUser->getUserPrincipalName(),'@');
            $commonName = substr($adUser->getUserPrincipalName(),0,($i));

            $attributes = array();
            $attributes['cn'] = $commonName;
            $attributes['sAMAccountName'] = $commonName;
            $attributes['userPrincipalName'] = $adUser->getUserPrincipalName();
            $attributes['givenname'] = $adUser->getFirstName();
            $attributes['sn'] = $adUser->getLastName();
            $attributes['displayName'] = $adUser->getLastName() . '_' . $adUser->getFirstName() . '_(' . $commonName . ')';
            if ($adUser->getOfficialAddress()) {
                if ($adUser->getOfficialAddress()->getCity()) {
                    $attributes['l'] = $adUser->getOfficialAddress()->getCity();
                }
                if ($adUser->getOfficialAddress()->getPostCode()) {
                    $attributes['postalCode'] = $adUser->getOfficialAddress()->getPostCode();
                }
                if ($adUser->getOfficialAddress()->getStreetName()) {
                    $attributes['streetAddress'] = $adUser->getOfficialAddress()->getStreetName() . ' ' . $adUser->getOfficialAddress()->getStreetNumber();
                }
            }
            $attributes['mail'] = $commonName . '@school.be';
            $attributes['employeeid'] = $adUser->getAdministrativeId();
            $attributes['distinguishedName'] = 'cn='.$commonName.',ou=example,dc=school,dc=be';
            // This attribute is used to enable or disable an account, default the account is disabled so we will need to change the default value of 546 to 512
            //$attributes['userAccountControl'] = 512;
            $attributes['userPassword'] = "{SHA}" . base64_encode( pack( "H*", sha1( 'examplepassword' ) ) );

            // de attributen hieronder zijn vereist om de functie te laten werken.
            $attributes["objectclass"][0] = "top";
            $attributes["objectclass"][1] = "person";
            $attributes["objectclass"][2] = "organizationalPerson";
            $attributes["objectclass"][3] = "user";

            $success = $this->dataStore->addUser($attributes);
        }
        $this->dataStore->disconnect();
        return $success;
    }
    public function addToGroup($adUser,$adGroupDn) {
        $success = false;
        print_r($this->adConnectorInfo);
        if($this->dataStore->connect()) {

            $i = strrpos($adUser->getUserPrincipalName(),'@');
            $commonName = substr($adUser->getUserPrincipalName(),0,($i));

            $attributes = array();
            $attributes['member'] = 'CN='.$commonName.',ou=example,dc=school,dc=be';
            $success = $this->dataStore->addToGroup($adGroupDn, $attributes);
        }
        $this->dataStore->disconnect();
        return $success;
    }
}