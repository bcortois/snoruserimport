<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 13-06-18
 * Time: 13:14
 */

namespace Snor\UserImport\Dal;


class AdConnectorInfo
{
    private $domainController;
    private $privilegedUserDn;
    private $privilegedUserPassword;
    private $searchBaseDn;
    private $ldapFilter;
    private $attributes;

    /**
     * AdConnectorInfo constructor.
     * @param $domainController
     * @param $privilegedUserDn
     * @param $privilegedUserPassword
     */
    public function __construct($domainController, $privilegedUserDn, $privilegedUserPassword)
    {
        $this->domainController = $domainController;
        $this->privilegedUserDn = $privilegedUserDn;
        $this->privilegedUserPassword = $privilegedUserPassword;
    }

    /**
     * @return mixed
     */
    public function getDomainController()
    {
        return $this->domainController;
    }

    /**
     * @param mixed $domainController
     */
    public function setDomainController($domainController)
    {
        $this->domainController = $domainController;
    }

    /**
     * @return mixed
     */
    public function getPrivilegedUserDn()
    {
        return $this->privilegedUserDn;
    }

    /**
     * @param mixed $privilegedUserDn
     */
    public function setPrivilegedUserDn($privilegedUserDn)
    {
        $this->privilegedUserDn = $privilegedUserDn;
    }

    /**
     * @return mixed
     */
    public function getPrivilegedUserPassword()
    {
        return $this->privilegedUserPassword;
    }

    /**
     * @param mixed $privilegedUserPassword
     */
    public function setPrivilegedUserPassword($privilegedUserPassword)
    {
        $this->privilegedUserPassword = $privilegedUserPassword;
    }

    /**
     * @return mixed
     */
    public function getSearchBaseDn()
    {
        return $this->searchBaseDn;
    }

    /**
     * @param mixed $searchBaseDn
     */
    public function setSearchBaseDn($searchBaseDn)
    {
        $this->searchBaseDn = $searchBaseDn;
    }

    /**
     * @return mixed
     */
    public function getLdapFilter()
    {
        return $this->ldapFilter;
    }

    /**
     * @param mixed $ldapFilter
     */
    public function setLdapFilter($ldapFilter)
    {
        $this->ldapFilter = $ldapFilter;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function setSearchScope($seachBaseDn, $ldapFilter, $attributes) {
        $this->searchBaseDn = $seachBaseDn;
        $this->ldapFilter = $ldapFilter;
        $this->attributes = $attributes;
    }
}