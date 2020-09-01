<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 04-06-18
 * Time: 13:01
 */

namespace Snor\UserImport\Dal;


class AdConnector
{
    private $adConnectorInfo;
    private $ldapConnection;
    private $data;

    public function __construct($adConnectorInfo)
    {
        $this->adConnectorInfo = $adConnectorInfo;
        $this->ldapConnection = ldap_connect($this->adConnectorInfo->getDomainController());
    }

    public function getData() {
        return $this->data;
    }

    public function connect() {
        if ($this->ldapConnection) {
            return ldap_bind($this->ldapConnection, $this->adConnectorInfo->getPrivilegedUserDn(), $this->adConnectorInfo->getPrivilegedUserPassword());
        }
    }

    public function disconnect() {
        if ($this->ldapConnection) {
            ldap_unbind($this->ldapConnection);
        }
    }

    public function fetchUsers() {
        // $alfabet bevat voor elke letter een element, deze array wordt gebruikt om in de forloop over te ittereren.
        $alfabet = range('a', 'z');
        // de counter geeft het toaal aantal ADobjecten aan.
        $counter = 0;
        // De $result array zal alle resultaten van de ldap query bevatten
        $result = array();
        // Deze forloop gaat elke letter van het alfabet af en gebruikt deze als filter in de ldap_search functie.
        // Deze procedure is nodig aangezien je met de ldap_search op een AD server max 1000 rows kan binnen halen
        for ($i=0; $i<26;$i++) {
            // de filter wordt aangepast met de alfabetische letter in de itteratie
            if ($this->adConnectorInfo->getLdapFilter()) {
                $this->adConnectorInfo->setLdapFilter('(sn='.$alfabet[$i].'*)');
            }
            $searchResult = ldap_search($this->ldapConnection,$this->adConnectorInfo->getSearchBaseDn(),$this->adConnectorInfo->getLdapFilter(),$this->adConnectorInfo->getAttributes());
            ldap_sort($this->ldapConnection,$searchResult,'sn');
            $rows = ldap_get_entries($this->ldapConnection, $searchResult);
            $counter += $rows['count'];
            // De rows bevat nu alle AD objecten waarvan hun CN begint met de alfabetische letter van de itteratie. Deze objecten zijn aan de hand van numerieke sleutels aanwezig in de array.
            // De ldap_get_entries functie voegt ook een extra key toe, een associatieve met de naam ['count']. Deze key komt maar 1 keer voor in de resulterende array dat de functie terug geeft. het heeft als waarde een nummer.
            // aangezien we tijdens het ittereren in deze for loop de resulterende arrays gaan mergen met het vorige resultaat zal deze count key meer dan 1 keer terugkomen, daar om halen we ze er uit en voegen we ze na de loop terug toe
            // met het totaal van de counts.
            unset($rows['count']);
            $newResult = array_merge($result,$rows);
            $result = $newResult;
        }
        $result['count'] = $counter;
        $this->data =  $result;
    }

    public function modifyUser($dn, $attributes) {
        return ldap_modify($this->ldapConnection,$dn,$attributes);
    }

    public function samAccountNameExists($samAccountName) {

        $this->adConnectorInfo->setLdapFilter('(sAMAccountName='.$samAccountName.')');

        $searchResult = ldap_search($this->ldapConnection,$this->adConnectorInfo->getSearchBaseDn(),$this->adConnectorInfo->getLdapFilter(),$this->adConnectorInfo->getAttributes());
        ldap_sort($this->ldapConnection,$searchResult,'sn');
        $rows = ldap_get_entries($this->ldapConnection, $searchResult);
        if($rows['count'] > 0) {
            return true;
        }
    }

    public function userPrincipalNameExists($userPrincipalName) {

        $this->adConnectorInfo->setLdapFilter('(userPrincipalName='.$userPrincipalName.')');

        $searchResult = ldap_search($this->ldapConnection,$this->adConnectorInfo->getSearchBaseDn(),$this->adConnectorInfo->getLdapFilter(),$this->adConnectorInfo->getAttributes());
        ldap_sort($this->ldapConnection,$searchResult,'sn');
        $rows = ldap_get_entries($this->ldapConnection, $searchResult);
        if($rows['count'] > 0) {
            return true;
        }
    }

    public function addUser($attributes) {
        // add data to directory
        $success = ldap_add($this->ldapConnection, $attributes['distinguishedName'], $attributes);
        return $success;
    }
    public function addToGroup($adGroupDn,$attributes) {
        ldap_mod_add($this->ldapConnection,$adGroupDn,$attributes);
    }
}