<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 12-06-18
 * Time: 10:07
 */

namespace Snor\UserImport\Bll;


class AdUser
{
    private $administrativeId;
    private $firstName;
    private $lastName;
    // obj
    private $officialAddress;
    private $emailAddress;
    private $department;

    private $userPrincipalName;
    private $schoolName;
    private $role;
    private $synced;

    /**
     * @return mixed
     */
    public function getSynced()
    {
        return $this->synced;
    }

    /**
     * @param mixed $synced
     */
    public function setSynced($synced)
    {
        $this->synced = $synced;
    }

    /**
     * @return mixed
     */
    public function getAdministrativeId()
    {
        return $this->administrativeId;
    }

    /**
     * @param mixed $administrativeId
     */
    public function setAdministrativeId($administrativeId)
    {
        $this->administrativeId = $administrativeId;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * The string gets trimmed
     */
    public function setFirstName($firstName)
    {
        $this->firstName = trim($firstName);
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * The string gets trimmed
     */
    public function setLastName($lastName)
    {
        $this->lastName = trim($lastName);
    }

    /**
     * @return mixed
     */
    public function getOfficialAddress()
    {
        return $this->officialAddress;
    }

    /**
     * @param mixed $officialAddress
     */
    public function setOfficialAddress($officialAddress)
    {
        $this->officialAddress = $officialAddress;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getUserPrincipalName()
    {
        return $this->userPrincipalName;
    }

    /**
     * @param mixed $userPrincipalName
     */
    public function setUserPrincipalName($userPrincipalName)
    {
        $this->userPrincipalName = $userPrincipalName;
    }

    /**
     * @return mixed
     */
    public function getSchoolName()
    {
        return $this->schoolName;
    }

    /**
     * @param mixed $schoolName
     */
    public function setSchoolName($schoolName)
    {
        $this->schoolName = $schoolName;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    public function callMethodByName($methodName, $type, $value) {
        $method = $type . $methodName;
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

}