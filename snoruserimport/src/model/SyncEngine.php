<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 13-06-18
 * Time: 12:58
 */

namespace Snor\UserImport\Model;


class SyncEngine
{
    private $adImport;
    private $informatImport;

    /**
     * SyncEngine constructor.
     * @param $adImport
     * @param $wisaImport
     */
    public function __construct($adConnectorInfo, $apiConnectionInfo, $config)
    {
        $this->adImport = new \Snor\UserImport\Model\AdImport($adConnectorInfo, $config);
        $this->informatImport = new \Snor\UserImport\Model\InformatImport($apiConnectionInfo, $config);
    }


    public function wisaToAd($report) {
        /**
         * DEPRACTED
         * 26/05/2020: Deze functie was al enige tijd niet meer in gebruik. Er zijn hier en daar nog verwijzingen naar deze code.
         * Er werkden enkele values verwijderd omwille van security issues. Deze functie staat hier louter informatief voor het geval er nog referenties gevonden worden.
         **/
        //$adImport = new \Snor\UserImport\Model\AdImport($adConnectorInfo);
        $this->adImport->fetchAdUsers();
        $adUsers = $this->adImport->getResult();

        //$this->wisaImport = new \Snor\UserImport\Model\WisaImport($wisaConnectionInfo);

        // De extra param is enkel nodig omwille van de fix voor 7e jaars tijdens deletion van AD users, zie de regels hieronder
        $this->informatImport->fetchStudents(false);
        $wisaUsers = $this->informatImport->getResult();

        ////

        // onderstaande is een fix om ook klassen van het 7e jaar BSO op te nemen in SO.
        // deze fix is enkel te gebruiken voor het verwijderen van usersNotAttendingSchool uit AD
        // tijdelijk fix!!! geen goed design

        $newWisaConnectorInfo = new \Snor\UserImport\Dal\WisaConnectorInfo();
        $this->informatImport->setWisaConnectionInfo($newWisaConnectorInfo);
        $this->informatImport->fetchStudents(true);

        ////

        $wisaUsers = $this->informatImport->getResult();

        $notAttendingUsers = $this->adImport->getResult();
        /*echo 'totaal AD: ' . count($adUsers) . '<br>';
        echo 'eerste in AD: ' . $adUsers[0]->getFirstName() . ' ' . $adUsers[0]->getLastName() . ' ' . '<br>';
        print_r($adUsers[count($adUsers)-1]);
        //echo 'laatste in AD: ' . $adUsers[count($adUsers)-1]->getFirstName() . ' ' . $adUsers[count($adUsers)-1]->getLastName() . ' ' . '<br>';*/
        foreach ($wisaUsers as $a) {
            /* De eerste if hieronder controleert of de loopbaan van het huidig schooljaar van de leerling pas einde schooljaar eindigd,
            is dat niet het geval dan betekent dat de leerling al uitgeschreven is en niet meer op school loopt op de snor.
            de substr functie haalt enkel het gedeelte van maand dag en uur uit de propertie, het jaartal wordt niet gebruikt in de vergelijking.
            */

            /*
             update 02/04/2019:
                Onderstaande if conditie is in commentaar gezet.
                De voorwaarde dat deze if implementeerd is niet meer van toepassing. deze filter is verplaatst naar de dal klasse WisaImport.
                Na enige tijd mag deze conditie incl commentaar verwijderden.
            if (substr($a->getCourseEndDate(),5) == '06-30 00:00:00') {
            */
                $skipArr = Array();
                $match = FALSE;
                for ($i = 0; $i < count($adUsers); $i++) {
                    $wisaLastName = $a->getLastName();
                    $wisaFirstName = $a->getFirstName();
                    $adLastName = $adUsers[$i]->getLastName();
                    $adFirstName = $adUsers[$i]->getFirstName();

                    if (!(array_key_exists($i, $skipArr))) {
                        if ($a->getWisaId() == $adUsers[$i]->getAdministrativeId())
                        {
                            // Indien de wisa id in beide objecten overeen komt dan krijg je een onvoorwaardelijke match.
                            $report->match(new \Snor\UserImport\Model\Match($a, $adUsers[$i]));
                            $match = TRUE;

                            // De $notAttendingUsers bevat initieel alle AD users. wanneer er een match wordt gevonden zal de onderste code kijken of deze aduser nog in de $notAttendingUsers lijst
                            // voorkomt, zoja dan wordt deze eruit verwijderd. Aan het einde van het uitvoeren van deze functie blijven enkel de Adusers die verwijderd mogen worden in de $notAttendingUsers lijst over
                            if(($keyAdUser = array_search($adUsers[$i], $notAttendingUsers))!== FALSE) {
                                //echo $notAttendingUsers[$keyAdUser]->getFirstName().' '.$notAttendingUsers[$keyAdUser]->getLastName().'<br>';
                                unset($notAttendingUsers[$keyAdUser]);
                            }
                            break;
                        }
                        if (strcasecmp($wisaLastName, $adLastName) == 0) {
                            // strcascmp retourneerd '0' als beide strings hetzelfde zijn, zonder rekening te houden met hoofdletters/klein letters.
                            // De code binnen deze conditionblock zal uitgevoerd worden als de achternaam van zowel het wisa obj als ad obj hetzelfde zijn.
                            if (strcasecmp($wisaFirstName, $adFirstName) == 0) {
                                // De code binnen deze conditionblock zal uitgevoerd worden als de voornaam van zowel het wisa obj als ad obj hetzelfde zijn.
                                if (!$adUsers[$i]->getAdministrativeId()) {
                                    // als extra controle op de overeenkomst van beide users wordt gekeken of het wisaid veld van de aduser leeg is. Als dat wel het geval is
                                    // dan kan er geen match op basis van id gebeuren en moet deze weldegelijk op basis van voornaam+naam vastgesteld worden. In dat geval wordt onderstaande code uitgevoerd en wordt
                                    // het aanzien als een positive match.
                                    // OPMERKING: dit systeem is niet 100% in het geval dat er ADusers zijn zonder ingevuld wisaid. Er kunnen namelijk 2 verschillende personen over dezelfde voor+achternaam beschikken.
                                    // daarom is het van belang dat alle users in AD al gematched kunnen worden op basis van wisaID. (Hardmatch vs softmatch).
                                    $report->match(new \Snor\UserImport\Model\Match($a, $adUsers[$i]));
                                    $match = TRUE;

                                    // De $notAttendingUsers bevat initieel alle AD users. wanneer er een match wordt gevonden zal de onderste code kijken of deze aduser nog in de $notAttendingUsers lijst
                                    // voorkomt, zoja dan wordt deze eruit verwijderd. Aan het einde van het uitvoeren van deze functie blijven enkel de Adusers die verwijderd mogen worden in de $notAttendingUsers lijst over
                                    if(($keyAdUser = array_search($adUsers[$i], $notAttendingUsers))!== FALSE) {
                                        //echo $notAttendingUsers[$keyAdUser]->getFirstName().' '.$notAttendingUsers[$keyAdUser]->getLastName().'<br>';
                                        unset($notAttendingUsers[$keyAdUser]);
                                    }
                                    break;
                                }
                                else {
                                    // is het wisaid veld van de aduser wel ingevuld, dan betekend het dan er geen juiste match (hardmatch) gevonden is. In dat geval wordt de huidige aduser toegevoegd
                                    // aan de skip array zodat deze vergelijking niet opnieuw gebeurt (elke loop iterratie wordt de volledige
                                    $skipArr[] = $i;
                                    continue;
                                }
                            }
                            else {
                                //echo 'skipped'.$adUsers[$i]['sn'][0].'<br>';
                                $skipArr[] = $i;
                                continue;
                            }
                        }
                        else {
                        }
                        continue;
                    }
                }
                // Geen indicatie, bevat dubbels.
                if (!$match) {
                    $report->notInAd($a);
                }
            /* update 02/04/2019:
                Onderstaande else block is in commentaar gezet omdat de if waartoe deze behoort niet meer in gebruik is (ook in commentaar).
                De code in de else-block was ook niet meer nuttig aangezien de WisaImport dal class aangepast werd zodat enkel de ingeschreven studenten eruit gefilterd worden.
                Na enige tijd mag deze block incl commentaar verwijderden.
            }

            else {
                $report->studentNotAttending($a);
            }
            */
        }
        /*for ($i = 0; $i < count($notAttendingUsers); $i++) {
            // onderstaande test of het een test leerling is, zo ja, dan mag deze NIET verwijderd worden
            print_r($notAttendingUsers[$i]);
            if (substr($notAttendingUsers[$i]->getUserPrincipalName(),0,3) == 'xx0' || substr($notAttendingUsers[$i]->getUserPrincipalName(),0,3) == 'tl4') {
                unset($notAttendingUsers[$i]);
            }
        }*/
        $report->setUsersNotAttendingSchool($notAttendingUsers);

    }

    public function SyncMatches($report) {
        // deze functie overloopt de match objecten die opgeslagen zijn in het report obj. Deze match obj's bevatten de userobj's (AdUser en Student) die volgens de functie wisaToAd overeenstemmend zijn.
        // Bij het overlopen zal het nagaan welke properties van het AdUser obj niet overeenkomen en zal deze vervolgens bijwerken met de gegevens vanuit het Student obj.
        foreach ($report->getMatches() as $match) {
            // De conditionblock hieronder zal enkel uitgevoerd worden in het geval de 'approved' property van het match obj false is. Deze logica is tijdelijke en
            // zal in de toekomst aangepast worden naar '== true'. Maar aangezien er nog geen GUI is die de waarde van deze propertie kan manipuleren zal hij altijd op zijn default waarde blijven (false).
            // om het programma de testen moet deze dus nog niet voldoen aan true.
            if ($match->getApproved() == false) {
                if (!$match->getDifferenceObj()->getAdministrativeId()) {
                    $match->getDifferenceObj()->setAdministrativeId($match->getReferenceObj()->getWisaId());
                    // We slagen de naam van de getmethod op in een array van het object report.
                    // Zo kunnen we later deze gegevens overlopen om zo de waarde van de properties die aangepast zijn na de sync, gemakkelijk terug te vinden.
                    $match->addUpdate('getWisaId');
                }
                if ($match->getReferenceObj()->getFirstName() != $match->getDifferenceObj()->getFirstName()) {
                    $match->getDifferenceObj()->getFirstName($match->getReferenceObj()->getFirstName());
                    // We slagen de naam van de getmethod op in een array van het object report.
                    // Zo kunnen we later deze gegevens overlopen om zo de waarde van de properties die aangepast zijn na de sync, gemakkelijk terug te vinden.
                    $match->addUpdate('getFirstName');
                }
                if ($match->getReferenceObj()->getLastName() != $match->getDifferenceObj()->getLastName()) {
                    $match->getDifferenceObj()->getLastName($match->getReferenceObj()->getLastName());
                    // We slagen de naam van de getmethod op in een array van het object report.
                    // Zo kunnen we later deze gegevens overlopen om zo de waarde van de properties die aangepast zijn na de sync, gemakkelijk terug te vinden.
                    $match->addUpdate('getLastName');
                }
                if($match->getDifferenceObj()->getEmailAddress() != $match->getDifferenceObj()->getUserPrincipalName()) {
                    $match->getDifferenceObj()->setEmailAddress($match->getDifferenceObj()->getUserPrincipalName());
                    // We slagen de naam van de getmethod op in een array van het object report.
                    // Zo kunnen we later deze gegevens overlopen om zo de waarde van de properties die aangepast zijn na de sync, gemakkelijk terug te vinden.
                    $match->addUpdate('getEmailAddress');
                }


                // onderstaande code is voor personeelsleden, waar het mailadres gemaakt moet worden a.d.h.v. de voor- en achternaam.
                // De code maakt een mail adres van deze 2 velden en checkt of het een valid mail adres is.
                // als dat niet het geval is blijft het mailadres veld leeg.
                /*$firstNameWithoutSpaces = str_replace(' ', '', $match->getReferenceObj()->getFirstName());
                $lastNameWithoutSpaces = str_replace(' ', '', $match->getReferenceObj()->getLastName());
                $emailAddress = $firstNameWithoutSpaces.'.'.$lastNameWithoutSpaces.'@student.snorduffel.be';
                if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    $emailAddressToLowerCase = strtolower($emailAddress);
                    if($match->getDifferenceObj()->getEmailAddress() != $emailAddressToLowerCase) {
                        $match->getDifferenceObj()->setEmailAddress($emailAddressToLowerCase);
                        // We slagen de naam van de getmethod op in een array van het object report.
                        // Zo kunnen we later deze gegevens overlopen om zo de waarde van de properties die aangepast zijn na de sync, gemakkelijk terug te vinden.
                        $match->addUpdate('getEmailAddress');
                    }
                }*/
            }
        }
    }

    private function getAllByIdAndName($adUsers,$id,$firstname,$lastname) {
        $matchesById = array();
        $matchesByName = array();
        for ($i = 0; $i < count($adUsers); $i++) {
            if ($adUsers[$i]->getAdministrativeId() == $id) {
                $matchesById[] = $adUsers[$i];
            }
            elseif (strcasecmp($adUsers[$i]->getLastName(),$lastname) == 0 && strcasecmp($adUsers[$i]->getFirstName(),$firstname) == 0) {
                $matchesByName[] = $adUsers[$i];
            }
        }
        return array("byId" => $matchesById, "byName" => $matchesByName);
    }

    public function informatToAd($report) {
        $this->informatImport->fetchStudents();
        $informatObjects = $this->informatImport->getResult();
        $this->adImport->fetchAdUsers();
        $adObjects = $this->adImport->getResult();
        $notAttendingUsers = $this->adImport->getResult();
        foreach($informatObjects as $studentObject) {
            //Oude manier, nog compatible
            //$matchedByName = $this->getAllByName($adObjects, $studentObject->getFirstName(), $studentObject->getLastName());
            //$matchedById = $this->getAllById($adObjects, $studentObject->getWisaId());

            //nieuwe manier
            $matches = $this->getAllByIdAndName($adObjects, $studentObject->getWisaId(), $studentObject->getFirstName(), $studentObject->getLastName());
            $matchedByName = $matches['byName'];
            $matchedById = $matches['byId'];

            if(!empty($matchedById)) {
                // Hard matching procedure
                // Er zijn id matched kandidaten
                // de loop hieronder checked of er een onvoorwaardelijke HARD match kan gemaakt worden.
                // is dat niet het geval dan is een menslijke controle vereist

                foreach($matchedById as $adUser) {

                    if ($adUser->getSynced() == true) {
                        // onvoorwaardelijke match
                        $report->match(new \Snor\UserImport\Model\Match($studentObject, $adUser));
                        // De $notAttendingUsers bevat initieel alle AD users. wanneer er een match wordt gevonden zal de onderste code kijken of deze aduser nog in de $notAttendingUsers lijst
                        // voorkomt, zoja dan wordt deze eruit verwijderd. Aan het einde van het uitvoeren van deze functie blijven enkel de Adusers die verwijderd mogen worden in de $notAttendingUsers lijst over
                        // UPDATE: 10-10-2019:
                        // Deze code zoekt in de $notAttendingUser array een een identiek object als $adUser om zo hetzelfde adUser object te vinden. De kans bestaat er 2 adUser objecten in de $notAttendingUsers array identiek zijn (propertyvalues)
                        // Daarom kan het zijn dat deze manier van zoeken niet 100% werkt. Eventuele oplossing is de GUID van adobjecten uit AD halen en in de AdUser klasse opnemen als een property en
                        //daar een vergelijking op maken. Nog te implementeren en testen.
                        if(($keyAdUser = array_search($adUser, $notAttendingUsers))!== FALSE) {
                            unset($notAttendingUsers[$keyAdUser]);
                        }
                        // deze continue start met een volgende iteratie van de foreach 2 niveaus hoger.
                        continue 2;
                    }
                    // De onderstaande if haalt de gebruikers die nog overblijven in de $matchedByName array uit de $notattendingUsers array omdat het nog niet geweten is of ze al dan niet nog op school zitten.
                    // Er dient eerst nog een melnselijke validatie te gebeuren met deze gebruikers alvorens te bepalen of het om een match gaat, of een een account van een leerling die neit meer op school is (front-end).
                    // UPDATE: 10-10-2019:
                    // Deze code zoekt in de $notAttendingUser array een een identiek object als $adUser om zo hetzelfde adUser object te vinden. De kans bestaat er 2 adUser objecten in de $notAttendingUsers array identiek zijn (propertyvalues)
                    // Daarom kan het zijn dat deze manier van zoeken niet 100% werkt. Eventuele oplossing is de GUID van adobjecten uit AD halen en in de AdUser klasse opnemen als een property en
                    //daar een vergelijking op maken. Nog te implementeren en testen.
                    if(($keyAdUser = array_search($adUser, $notAttendingUsers))!== FALSE) {
                        unset($notAttendingUsers[$keyAdUser]);
                    }
                }
            }
            if(!empty($matchedByName)) {
                // softmatching procedure
                // Er zijn enkel name-based matched kandidaten
                // de loop hieronder checked of er adusers zijn die onvoorwaardelijk GEEN MATCH zijn. Zo ja, dan wordt deze verwijderd uit de lijst kandidaten op naam.
                // Als er na deze loop nog kandidaten overblijven, dan is een menslijke controle vereist
                foreach($matchedByName as $key => $adUser) {
                    if ((!empty($adUser->getAdministrativeId())) && $adUser->getSynced() == true) {
                        // Als de adUser wel over een ID beschikt (maar die is natuurlijk anders dan die van $studentObject) en ook gesynced werd door deze app
                        // dan is het al zeker dat het GEEN MATCH is.
                        unset($matchedByName[$key]);
                    }
                    // De onderstaande if haalt de gebruikers die nog overblijven in de $matchedByName array uit de $notattendingUsers array omdat het nog niet geweten is of ze al dan niet nog op school zitten.
                    // Er dient eerst nog een melnselijke validatie te gebeuren met deze gebruikers alvorens te bepalen of het om een match gaat, of een een account van een leerling die neit meer op school is (front-end).
                    // UPDATE: 10-10-2019:
                    // Deze code zoekt in de $notAttendingUser array een een identiek object als $adUser om zo hetzelfde adUser object te vinden. De kans bestaat er 2 adUser objecten in de $notAttendingUsers array identiek zijn (propertyvalues)
                    // Daarom kan het zijn dat deze manier van zoeken niet 100% werkt. Eventuele oplossing is de GUID van adobjecten uit AD halen en in de AdUser klasse opnemen als een property en
                    //daar een vergelijking op maken. Nog te implementeren en testen.
                    if(($keyAdUser = array_search($adUser, $notAttendingUsers))!== FALSE) {
                        unset($notAttendingUsers[$keyAdUser]);
                    }
                }
            }
            if(empty($matchedByName) && empty($matchedById)) {
                // als zowel de $matchedById als $matchedByName lege arrays zijn, dan zijn er geen matched kandidaten gevonden en is de student nog niet
                // toegevoegd aan Ad. In dat geval moet er een account aangemaakt worden.
                $report->notInAd($studentObject);
                continue;
            }
            // Als er geen onvoorwaardelijke match is gevonden maar wel kandidaten, dan wordt onderstaande code uitgevoerd.
            // door als 3e parameter true aan te geven, geef je mee dat er meerdere kandidaten zijn en dus geen absolute match gevonden werd.
            // Dit kan in de front-end in een review voor de gebruiker gestopt worden.
            $candidates = array_merge($matchedByName,$matchedById);
            if (count($candidates) > 1) {
                $report->match(new \Snor\UserImport\Model\Match($studentObject, $candidates, true));
            }
            else {
                // Als deze else uitgevoerd wordt dan weten we dat er minstens en ook niet meer dan 1 aduser in de candidates array zit.
                // in dat geval voegen we de adusers als object toe en niet als element in een array. Ook de multipleCandidates param staat op false.
                $report->match(new \Snor\UserImport\Model\Match($studentObject, $candidates[0], false));
            }
        }
        $report->setUsersNotAttendingSchool($notAttendingUsers);
    }

    public function adUserExists($samAccountName,$adConnectorInfo) {
        //$adImport = new \Snor\UserImport\Model\AdImport($adConnectorInfo);
        return $this->adImport->userExists($samAccountName);
    }

    public function addUser($adUser,$adConnectorInfo) {
        //$adImport = new \Snor\UserImport\Model\AdImport($adConnectorInfo);
        $success = $this->adImport->addUser($adUser);
        return $success;
    }

    public function addToGroup($adUser,$adGroupDn,$adConnectorInfo) {
        //$adImport = new \Snor\UserImport\Model\AdImport($adConnectorInfo);
        $success = $this->adImport->addToGroup($adUser,$adGroupDn);
        return $success;
    }
}