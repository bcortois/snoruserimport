<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 29-05-18
 * Time: 16:40
 */

namespace Snor\UserImport\Bll;


class Student
{
    private $wisaId;
    private $firstName;
    private $lastName;
    private $birthDate;
    // obj
    private $officialAddress;
    private $emailAddress;
    private $gender;
    private $classCode;
    // de property className werd vervangen door classCode aangezien dit beter overeenstemd met de naamgeving in Informat. Hoewel de API het veld onder Klas invult, zal je in de Informat GUI de naamgeving Klascode terugvinden voor dit veld.
    private $className;
    private $studentClassGroup;
    private $studentClassNumber;
    private $courseEndDate;
    private $establishmentCode;

    /**
     * @return mixed
     */
    public function getClassCode()
    {
        return $this->classCode;
    }

    /**
     * @param mixed $classCode
     */
    public function setClassCode($classCode)
    {
        $this->classCode = $classCode;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentCode()
    {
        return $this->establishmentCode;
    }

    /**
     * @param mixed $establishmentCode
     */
    public function setEstablishmentCode($establishmentCode)
    {
        $this->establishmentCode = $establishmentCode;
    }

    /**
     * @return mixed
     */
    public function getWisaId()
    {
        return $this->wisaId;
    }

    /**
     * @param mixed $wisaId
     */
    public function setWisaId($wisaId)
    {
        $this->wisaId = $wisaId;
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
        // Deze functie verwijderd de whitespaces alvorens de input op te slagen in de property
        // Dit is nodig omdat er soms fouten worden gemaakt bij het toevoegen van leerlingen in wisa, met als gevolg dat er voor of achter de naam een spacie komt te slaan.
        // In geval van een softmatch waarbij de voor- en achternaam van een wisa record wordt vergeleken met eentje uit AD kan dit voor foutieve resultaten zorgen.
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
     */
    public function setLastName($lastName)
    {
        // Deze functie verwijderd de whitespaces alvorens de input op te slagen in de property
        // Dit is nodig omdat er soms fouten worden gemaakt bij het toevoegen van leerlingen in wisa, met als gevolg dat er voor of achter de naam een spacie komt te slaan.
        // In geval van een softmatch waarbij de voor- en achternaam van een wisa record wordt vergeleken met eentje uit AD kan dit voor foutieve resultaten zorgen.
        $this->lastName = trim($lastName);
    }

    /**
     * @return mixed
     */
    public function getStudentClassNumber()
    {
        return $this->studentClassNumber;
    }

    /**
     * @param mixed $studentClassNumber
     */
    public function setStudentClassNumber($studentClassNumber)
    {
        $this->studentClassNumber = $studentClassNumber;
    }

    /**
     * @return mixed
     */
    public function getStudentClassGroup()
    {
        return $this->studentClassGroup;
    }

    /**
     * @param mixed $studentClassGroup
     */
    public function setStudentClassGroup($studentClassGroup)
    {
        $this->studentClassGroup = $studentClassGroup;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
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
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getCourseEndDate()
    {
        return $this->courseEndDate;
    }

    /**
     * @param mixed $courseEndDate
     */
    public function setCourseEndDate($courseEndDate)
    {
        $this->courseEndDate = $courseEndDate;
    }

}