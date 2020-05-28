<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 07-05-19
 * Time: 15:06
 */

namespace Snor\UserImport\Model;


class InformatImport
{
    private $dataStore;
    private $result;
    private $informatConnectionInfo;

    public function __construct($informatConnectionInfo)
    {
        $this->informatConnectionInfo = $informatConnectionInfo;
        $this->result = Array();
    }

    private function initDal($connector) {
        $this->dataStore = new \Snor\UserImport\Dal\InformatConnector($connector);
    }

    public function getResult()
    {
        return $this->result;
    }

    private function isInList($id) {
        foreach ($this->result as $row) {
            if($row->getWisaId() == $id)
            {
                return TRUE;
            }
        }
    }


    public function fetchStudents() {
        if (is_array($this->informatConnectionInfo)) {
            foreach ($this->informatConnectionInfo as $connector) {
                $this->initDal($connector);
                $this->dataStore->fetch();
                $xmlObject = $this->dataStore->getData();

                /*foreach ($xmlObject as $obj) {
                    echo mb_detect_encoding($obj['p_persoon']).' '.$obj->p_persoon.', ';
                    echo mb_detect_encoding($obj['Naam']).' '.$obj->Naam.', ';
                    echo mb_detect_encoding($obj['Voornaam']).' '.$obj->Voornaam.', ';
                    echo mb_detect_encoding($obj['geboortedatum']).' '.$obj->geboortedatum.', ';
                    echo mb_detect_encoding($obj['geslacht']).' '.$obj->geslacht.', ';
                    echo mb_detect_encoding($obj['Klas']).' '.$obj->Klas.', ';
                    echo mb_detect_encoding($obj['afdcode']).' '.$obj->afdcode.', ';
                    echo mb_detect_encoding($obj['Klasnr']).' '.$obj->Klasnr.', ';
                    echo mb_detect_encoding($obj['vestcode']).' '.$obj->vestcode.'<br><br>';
                }*/

                $json = json_encode($xmlObject);
                $data = json_decode($json, true);
                $array = $data['wsInschrijving'];
                foreach ($array as $row) {
                    /*echo mb_detect_encoding($row['p_persoon']).' '.$row['p_persoon'].', ';
                    echo mb_detect_encoding($row['Naam']).' '.$row['Naam'].', ';
                    echo mb_detect_encoding($row['Voornaam']).' '.$row['Voornaam'].', ';
                    echo mb_detect_encoding($row['geboortedatum']).' '.$row['geboortedatum'].', ';
                    echo mb_detect_encoding($row['geslacht']).' '.$row['geslacht'].', ';
                    echo mb_detect_encoding($row['Klas']).' '.$row['Klas'].', ';
                    echo mb_detect_encoding($row['afdcode']).' '.$row['afdcode'].', ';
                    echo mb_detect_encoding($row['Klasnr']).' '.$row['Klasnr'].', ';
                    echo mb_detect_encoding($row['vestcode']).' '.$row['vestcode'].'<br><br>';*/

                    $student = new \Snor\UserImport\Bll\Student();
                    $student->setWisaId($row['p_persoon']);
                    $student->setLastName($row['Naam']);
                    //$student->setFirstName(iconv('ASCII', 'UTF-8//TRANSLIT', $row['Voornaam']));
                    $student->setFirstName($row['Voornaam'], "UTF-8");
                    $student->setBirthDate($row['geboortedatum']);
                    $student->setGender($row['geslacht']);
                    $student->setClassCode($row['Klas']);
                    //$student->setClassName($row['Klas']);
                    $student->setStudentClassGroup($row['afdcode']);
                    $student->setStudentClassNumber($row['Klasnr']);
                    $student->setEstablishmentCode($row['vestcode']);

                    // deze onderste 3 lijnen zijn niet noodzakelijk en tijdelijk. ze waren gebruikt om na te gaan hoe er best een code voor papercut aanmelding werd gegenereerd.
                    // Dit staat in Onenote gedocumenteerd onder implementatie documentatie.
                    // dit moet op een andere plek in deze app geintegreerd worden zodat er bij het aanmaken van een nieuwe leelring ook een nieuwe id wordt gegenereerd.
                    // deze id's zijn uniek aangezien het informat id ook uniek is en er als salt telkens met het zelfde getal wordt vermeerderd (1384).
                    //$printCode = 131986 + (int) $row['p_persoon'];
                    //echo gettype($printCode).' ';
                    //echo $printCode .'<br>';

                    $this->result[] = $student;
                    /*$ary[] = "ASCII";
                    $ary[] = "UTF-8";
                    echo mb_detect_encoding($student->getFirstName(),$ary);*/
                }
            }
        }
    }

    public function fetchDummyStudents($amount) {
        // deze var bevat de timestamp value terwaarde van 24u of 1 dag.
        $dateCounter = 86400;
        for ($i = 0; $i < $amount; $i++) {
            $student = new \Snor\UserImport\Bll\Student();
            $student->setWisaId('0000'.$i);
            $student->setLastName('import'.$i);
            //$student->setFirstName(iconv('ASCII', 'UTF-8//TRANSLIT', $row['Voornaam']));
            $student->setFirstName('leerling'.$i);
            // de functie date creëert een string in een gewenste date notatie.
            // De waarde is een timestamp die verhoogt wordt met de waarde van $dateCounter, elke iteratie wordt de datum dus verlaat met 1 dag.
            $student->setBirthDate(date("Y-m-d", time() + ($dateCounter)));
            //echo (date("Y-m-d", time() + ($dateCounter))) . ' ';
            $student->setGender('V');
            $student->setClassCode('klascode');
            //$student->setClassName($row['Klas']);
            $student->setStudentClassGroup('klasgroep');
            $student->setStudentClassNumber('klasnr');
            $student->setEstablishmentCode('vestiging');
            $this->result[] = $student;

            // De timestamp wordt verhoogt met 24 of 1 dag
            $dateCounter = $dateCounter + 86400;
        }
    }
}