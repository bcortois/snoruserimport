<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 12-06-18
 * Time: 15:45
 */

namespace Snor\UserImport\Bll;


class Report
{
    private $matches;
    private $notInAd;
    // leerlingen die in de import van wisa zitten maar het huidig school zijn uitgeschreven.
    private $studentsNotAttendingSchool;
    // ad users die niet ingeschreven zijn als student in het huidige schooljaar
    private $usersNotAttendingSchool;

    /**
     * Report constructor.
     */
    public function __construct()
    {
        $this->matches = Array();
        $this->notInAd = Array();
        $this->studentsNotAttendingSchool = Array();
        $this->usersNotAttendingSchool = Array();
    }

    public function match ($match) {
        $this->matches[] = $match;
    }

    public function notInAd($person) {
        $this->notInAd[] = $person;
    }

    public function studentNotAttending($person) {
        $this->studentsNotAttendingSchool[] = $person;
    }

    public function getStudentsNotAttendingSchool()
    {
        return $this->studentsNotAttendingSchool;
    }

    public function getMatches() {
        return $this->matches;
    }

    public function getNotInAd() {
        return $this->notInAd;
    }

    public function getMatchCount() {
        return count($this->matches);
    }

    public function getNotInAdCount() {
        return count($this->notInAd);
    }

    public function getUsersNotAttendingSchool()
    {
        return $this->usersNotAttendingSchool;
    }

    public function setUsersNotAttendingSchool($array)
    {
        $this->usersNotAttendingSchool = $array;
    }

    public function userNotAttending($user)
    {
        $this->usersNotAttendingSchool[] = $user;
    }
}