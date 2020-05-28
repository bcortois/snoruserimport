<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 07-05-19
 * Time: 14:49
 */

namespace Snor\UserImport\Dal;


class InformatConnectorInfo
{
    private $requestUri;
    private $username;
    private $password;
    private $schoolyear;
    private $institutionNumber;
    private $referenceDate;
    private $rootStructure;

    public function __construct($requestUri, $username, $password, $schoolyear, $institutionNumber, $referenceDate, $rootStructure)
    {
        $this->requestUri = $requestUri;
        $this->username = $username;
        $this->password = $password;
        $this->schoolyear = $schoolyear;
        $this->institutionNumber = $institutionNumber;
        $this->referenceDate = $referenceDate;
        $this->rootStructure = $rootStructure;
    }
    /**
     * @return mixed
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getSchoolyear()
    {
        return $this->schoolyear;
    }

    /**
     * @return mixed
     */
    public function getRootStructure()
    {
        return $this->rootStructure;
    }

    /**
     * @return mixed
     */
    public function getReferenceDate()
    {
        return $this->referenceDate;
    }

    /**
     * @return mixed
     */
    public function getInstitutionNumber()
    {
        return $this->institutionNumber;
    }

    /**
     * @param mixed $rootStructure
     */
    public function setRootStructure($rootStructure)
    {
        $this->rootStructure = $rootStructure;
    }

    /**
     * @param mixed $referenceDate
     */
    public function setReferenceDate($referenceDate)
    {
        $this->referenceDate = $referenceDate;
    }

    /**
     * @param mixed $requestUri
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $schoolyear
     */
    public function setSchoolyear($schoolyear)
    {
        $this->schoolyear = $schoolyear;
    }

    /**
     * @param mixed $institutionNumber
     */
    public function setInstitutionNumber($institutionNumber)
    {
        $this->institutionNumber = $institutionNumber;
    }
}