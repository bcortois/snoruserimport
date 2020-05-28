<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 26-06-18
 * Time: 13:01
 */

namespace Snor\UserImport\Model;


class Match
{
    private $approved;
    private $referenceObj;
    private $differenceObj;
    // update 10-10-2019 - zie commentaar constructor
    private $multipleCandidates;
    private $updates;

    // update 10-10-2019:
    // param $multipleCandidates werd toegevoegd om aan te geven dat er meerder matches mogelijk zijn en er dus menselijke validatie vereist is. Wanneer deze dus op true staat zal de $differenceObj param een array met meerdere AdUser objecten bevatten.
    //
    public function __construct($referenceObj, $differenceObj, $multipleCandidates = false)
    {
        $this->referenceObj = $referenceObj;
        $this->differenceObj = $differenceObj;
        $this->multipleCandidates = $multipleCandidates;
        // de property updates zal de namen van get methodes bevatten van de properties die aangepast zijn tijdens het syncen (van bv een AdUser object).
        $this->updates = Array();
        $this->approved = false;
    }

    /**
     * @return boolean
     */
    public function hasMultipleCandidates()
    {
        return $this->multipleCandidates;
    }

    /**
     * @param boolean $multipleCandidates
     */
    public function setMultipleCandidates($multipleCandidates)
    {
        $this->multipleCandidates = $multipleCandidates;
    }

    public function getUpdates()
    {
        return $this->updates;
    }

    public function addUpdate($getMethodName)
    {
        $this->updates[] = $getMethodName;
    }

    public function setUpdates($updates)
    {
        $this->updates = $updates;
    }

    public function getDifferenceObj()
    {
        return $this->differenceObj;
    }

    public function setDifferenceObj($differenceObj)
    {
        $this->differenceObj = $differenceObj;
    }

    public function getReferenceObj()
    {
        return $this->referenceObj;
    }

    public function setReferenceObj($referenceObj)
    {
        $this->referenceObj = $referenceObj;
    }

    public function getApproved()
    {
        return $this->approved;
    }

    public function setApproved($approved)
    {
        $this->approved = $approved;
    }
}