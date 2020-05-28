<?php

namespace Snor\UserImport\Bll;

class PostalAddress {
    private $streetName;
    private $streetNumber;
    private $mailboxNumber;
    private $postcode;
    private $city;
    private $landlinePhoneNumber;

    function __construct($streetName, $streetNumber, $mailboxNumber, $postcode, $city, $landlinePhoneNumber) {
        $this->streetName = $streetName;
        $this->streetNumber = $streetNumber;
        $this->mailboxNumber = $mailboxNumber;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->landlinePhoneNumber = $landlinePhoneNumber;
    }

    public function getStreetName() {
        return $this->streetName;
    }
    public function setStreetName($streetName) {
        $this->streetName = $streetName;
        return $this;
    }
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
    }
    public function getMailboxNumber()
    {
        return $this->mailboxNumber;
    }
    public function setMailboxNumber($mailboxNumber)
    {
        $this->mailboxNumber = $mailboxNumber;
    }
    public function getPostcode() {
        return $this->postcode;
    }
    public function setPostcode($postcode) {
        $this->postcode = $postcode;
        return $this;
    }
    public function getCity() {
        return $this->city;
    }
    public function setCity($city) {
        $this->city = $city;
        return $this;
    }
    public function getLandlinePhoneNumber() {
        return $this->landlinePhoneNumber;
    }
    public function setLandlinePhoneNumber($landlinePhoneNumber) {
        $this->landlinePhoneNumber = $landlinePhoneNumber;
        return $this;
    }
}