<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 12-06-18
 * Time: 10:20
 */

namespace Snor\UserImport\Model;


class WisaImport
{
    private $dataStore;
    private $result;
    private $wisaConnectionInfo;

    public function setWisaConnectionInfo($wisaConnectionInfo) {
        $this->dataStore->setApiConnectionInfo($wisaConnectionInfo);
    }

    public function __construct($wisaConnectionInfo)
    {
        $this->wisaConnectionInfo = $wisaConnectionInfo;
        $this->dataStore = new \Snor\UserImport\Dal\ApiConnector($this->wisaConnectionInfo);
        $this->result = Array();
    }

    public function getResult()
    {
        return $this->result;
    }

    private function isInList($wisaId) {
        foreach ($this->result as $row) {
            if($row->getWisaId() == $wisaId)
            {
                return TRUE;
            }
        }
    }


    public function fetchStudents($soFromHbo) {
        $this->dataStore->fetch();
        foreach(json_decode($this->dataStore->getData(), true) as $row) {
            /* update 02/04/2019:
                De code "substr($row['LB_TOT'],5) == '06-30 00:00:00'" werd toegevoegd aan de if om ervoor te zorgen dat het record met de juiste loopbaan wordt geselecteerd.
                Voor deze aanpassing was deze controle enkel aanwezig in een laag hoger (SyncEngine), maar aangezien hier al enkel 1 record per wisaid werd bijgehouden kon
                het gebeuren dat de foute werd bijgehouden (een record met een loopbaan dat al geëindigd is).
            */
            if ($row['LB_LAATST'] != '-' && substr($row['LB_TOT'],5) == '06-30 00:00:00') {
                if (!$this->isInList($row['LL_ID'])) {

                    // onderstaande if structuur is een tijdelijke fix om uit de dataset van HBO5 de 7e jaars te filteren. Deze fix is enkel nodig voor de deletion van AD users
                    // als de functie met param true wordt meegegeven dan betekent dat je de 7e jaars uit HBO5 nodig hebt en zal hij per record kijken of
                    // de klascode begint met 7. als de param niet true is of leeg dan betekent dat er een standaard dataset wordt gevraagd van so of hbo.
                    if ($soFromHbo) {

                        if (substr($row['KL_CODE'],0,1) == '7') {
                            echo 'test';
                            $wisaStudent = new \Snor\UserImport\Bll\Student();
                            $wisaStudent->setWisaId($row['LL_ID']);
                            $wisaStudent->setLastName($row['LL_NAAM']);
                            $wisaStudent->setFirstName($row['LL_VOORNAAM']);
                            $wisaStudent->setBirthDate($row['LL_GEBOORTEDATUM']);
                            $wisaStudent->setGender($row['LL_GESLACHT']);
                            $wisaStudent->setOfficialAddress(
                                new \Snor\UserImport\Bll\PostalAddress(
                                    $row['LA_STRAAT'],
                                    $row['LA_STRAATNR'],
                                    $row['LA_STRAATBUS'],
                                    $row['GM_POSTCODE'],
                                    $row['GM_DEELGEMEENTE'],
                                    $row['TELEFOON'])
                            );
                            $wisaStudent->setEmailAddress($row['LL_EMAIL']);
                            $wisaStudent->setClassName($row['KL_CODE']);
                            $wisaStudent->setStudentClassGroup($row['KG_CODE']);
                            $wisaStudent->setStudentClassNumber($row['LB_KLASNUMMER']);
                            $wisaStudent->setCourseEndDate($row['LB_TOT']);
                            $this->result[] = $wisaStudent;
                        }
                    }
                    else {
                        $wisaStudent = new \Snor\UserImport\Bll\Student();
                        $wisaStudent->setWisaId($row['LL_ID']);
                        $wisaStudent->setLastName($row['LL_NAAM']);
                        $wisaStudent->setFirstName($row['LL_VOORNAAM']);
                        $wisaStudent->setBirthDate($row['LL_GEBOORTEDATUM']);
                        $wisaStudent->setGender($row['LL_GESLACHT']);
                        $wisaStudent->setOfficialAddress(
                            new \Snor\UserImport\Bll\PostalAddress(
                                $row['LA_STRAAT'],
                                $row['LA_STRAATNR'],
                                $row['LA_STRAATBUS'],
                                $row['GM_POSTCODE'],
                                $row['GM_DEELGEMEENTE'],
                                $row['TELEFOON'])
                        );
                        $wisaStudent->setEmailAddress($row['LL_EMAIL']);
                        $wisaStudent->setClassName($row['KL_CODE']);
                        $wisaStudent->setStudentClassGroup($row['KG_CODE']);
                        $wisaStudent->setStudentClassNumber($row['LB_KLASNUMMER']);
                        $wisaStudent->setCourseEndDate($row['LB_TOT']);
                        $this->result[] = $wisaStudent;
                    }
                }
                else {
                    //echo 'Dubbel: '.$row['LL_ID']. ' ' .$row['LB_LAATST'].'<br>';
                }
            }
            else {
                echo 'WisaImport.php:'.'<br>';
                echo 'Niet laatste: '.$row['LL_ID']. ' ' .$row['LB_LAATST'].'<br>';
            }
        }
    }
}