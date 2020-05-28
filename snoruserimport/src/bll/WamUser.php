<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 25-06-19
 * Time: 15:52
 */

namespace Snor\UserImport\Bll;


class WamUser
{
    private $administrativeId;
    private $firstName;
    private $lastName;
    private $displayName;
    private $emailAddress;
    private $department;
    private $userPrincipalName;
    private $schoolName;
    private $role;
    private $samAccountName;
    private $path;
    private $enabled;
    private $changePasswordAtLogon;

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
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
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
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
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

    /**
     * @return mixed
     */
    public function getSamAccountName()
    {
        return $this->samAccountName;
    }

    /**
     * @param mixed $samAccountName
     */
    public function setSamAccountName($samAccountName)
    {
        $this->samAccountName = $samAccountName;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getChangePasswordAtLogon()
    {
        return $this->changePasswordAtLogon;
    }

    /**
     * @param mixed $changePasswordAtLogon
     */
    public function setChangePasswordAtLogon($changePasswordAtLogon)
    {
        $this->changePasswordAtLogon = $changePasswordAtLogon;
    }

    public function callGetMethodByName($methodName) {
        $method = 'get' . $methodName;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    public function jsonSerialize() {
        // $getterNames stores the names of all the methods of this class.
        $getterNames = get_class_methods(get_class($this));
        // $gettableAttributes will store the object's properties+ values as key value pairs (associative array)
        $gettableAttributes = array();
        foreach ($getterNames as $key => $value) {
            // The condition is met if the methode name start with 'get' (getters only)
            if(substr($value, 0, 3) === 'get') {
                try {
                    // With the code '$this->$value()' u can execute the method with the name specified as a string in $value. (e.g. $value = 'getId', this will make $this->getId())
                    // we check if the value that the getter returns is an array and if the length is greater that 0 (0 equals to false).
                    if (is_array($this->$value()) && count($this->$value())) {
                        // if the method returns an array, we make a new array to store the converted result.
                        $jsonList = array();
                        foreach ($this->$value() as $item) {
                            // if the element stored inside the array is an object, then we call the object's own jsonSerialze function and return the result into the resulting array.
                            if (is_object($item)) {
                                $jsonList[] = $item->jsonSerialize();
                            }
                        }
                        // When all the arrays elements are converted and stored into the resulting array, then we store the resulting array into the main resulting array as part of the json object's properties.
                        $gettableAttributes[lcfirst(substr($value, 3, strlen($value)))] = $jsonList;
                    }
                    // incase that the getter doesn't return a array, we check if it is an object, if so we call the object's own jsonserialize function and store the returned value into the json object array.
                    elseif (is_object($this->$value())) {
                        $gettableAttributes[lcfirst(substr($value, 3, strlen($value)))] = $this->$value()->jsonSerialize();
                    }
                    // if the getter doesn't return a array nor object, then we store the value inside the json array and use the getter name minus the leading 'get' as key.
                    else {
                        $gettableAttributes[lcfirst(substr($value, 3, strlen($value)))] = $this->$value();
                    }
                }
                catch(\Exception $ex) {
                    print_r($ex);
                }
            }
        }
        return $gettableAttributes;
    }
}