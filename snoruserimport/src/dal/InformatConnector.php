<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 29-04-19
 * Time: 15:11
 */

namespace Snor\UserImport\Dal;


class InformatConnector
{
    private $data;
    private $apiConnectionInfo;

    /**
     * ApiConnector constructor.
     * @param $apiConnectionInfo
     */
    public function __construct($apiConnectionInfo)
    {
        $this->apiConnectionInfo = $apiConnectionInfo;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function setApiConnectionInfo($apiConnectionInfo) {
        $this->apiConnectionInfo = $apiConnectionInfo;
    }

    public function getRequestParams() {
        //The data you want to send via POST
        $fields = [
            'login'      => $this->apiConnectionInfo->getUsername(),
            'paswoord' => $this->apiConnectionInfo->getPassword(),
            'schooljaar'         => $this->apiConnectionInfo->getSchoolyear(),
            'instelnr'         => $this->apiConnectionInfo->getInstitutionNumber(),
            'referentiedatum'         => $this->apiConnectionInfo->getReferenceDate(),
            'hoofdstructuur'         => $this->apiConnectionInfo->getRootStructure()
        ];
        return $fields;
    }

    public function fetch() {
        //url-ify the data for the POST
        $fields_string = http_build_query($this->getRequestParams());

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->apiConnectionInfo->getRequestUri());
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        //echo mb_detect_encoding($result) . '<br>';
        $xml = simplexml_load_string($result);

        // set document encoding to US-ASCII via DOMDocument
        //$doc = dom_import_simplexml($xml)->ownerDocument;
        //$doc->encoding = 'UTF-8';

        //echo mb_detect_encoding($xml->asXML) . '<br>';

        //$xml->asXML('php://output');



        /*foreach ($xml as $obj) {
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
        $this->data = $xml;
    }
}