<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 25-06-19
 * Time: 14:00
 */

namespace Snor\UserImport\Dal;


class WamConnector
{
    private $data;
    private $config;

    /**
     * ApiConnector constructor.
     * @param $apiConnectionInfo
     */
    public function __construct($config)
    {
        $this->config = $config;
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

    public function getRequestParams() {
        //The data you want to send via POST
        $fields = [
            'username'      => $this->config['active_directory']['user_dn'],
            'paswoord' => $this->config['active_directory']['wachtwoord'],
            'schooljaar'         => $this->apiConnectionInfo->getSchoolyear(),
            'instelnr'         => $this->apiConnectionInfo->getInstitutionNumber(),
            'referentiedatum'         => $this->apiConnectionInfo->getReferenceDate(),
            'hoofdstructuur'         => $this->apiConnectionInfo->getRootStructure()
        ];
        return $fields;
    }

    public function addUsers($users) {


        //url-ify de data voor de POST
        $dataString = json_encode($users);

        //open connection
        $ch = curl_init();

        //de url van de bestemming , aantal POST variabelen en POST data wordt ingesteld
        $serviceUri = $this->config['wam_api']['base_uri'].$this->config['wam_api']['resource_uri']['add_user'];
        curl_setopt($ch,CURLOPT_URL, $serviceUri);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['wam_api']['gebruiker'] . ":" . $this->config['wam_api']['wachtwoord']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch,CURLOPT_POSTFIELDS, $dataString);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //Onderstaande optie zorgt ervoor dat curl_exec de contents van de cURL retourneerd, inplaats van de contents te echo'en
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        // Als je content-type aanpast naar application/json dan geeft PHP 5.6 een warning ivm $HTTP_RAW_POST_DATA is deprecated en een notice over een header die al ingesteld werd.
        // Daarom werd deze header aangepast naar application/x-www-form-urlencoded.
        // meer info over het $HTTP_RAW_POST_DATA probleem in PHP 5.6 vindt je terug op deze website: https://github.com/matomo-org/matomo/issues/6465
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Content-Length: ' . strlen($dataString))
        );



        //execute post
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 0) {
            echo 'WamConnector: connection could not be established, no response from url <br />';
        }
        if ($httpcode == 201) {
            echo 'WamConnector: webapi call send <br />';
        }
        if ($httpcode == 401) {
            echo 'WamConnector: Unauthorized, either wrong credentials or access denied <br />';
        }
        return $result;

        /*
        $baseUri = $this->config['wam_api']['base_uri'];
        $guzzleClient = new \GuzzleHttp\Client(['base_uri' => $baseUri]);

        $postData = [
            'json' => [
                $users
            ],
            'auth' => [
                $this->config['wam_api']['gebruiker'],
                $this->config['wam_api']['wachtwoord']
            ]
        ];
        /*$jsonData = array();
        foreach($users as $user) {
            $jsonData[] = $user->jsonSerialize();
        }
        $response = $guzzleClient->post($this->config['wam_api']['resource_uri']['add_user'], $postData);
        echo $response->getStatusCode();
        $result = $response->getBody();
        return $result;*/
    }

    public function addUsers2() {
        $data = array("sam_account_name" => "cob", "first_name" => "Bert");
        $data_string = json_encode($data);

        $serviceUri = $this->config['wam_api']['base_uri'].$this->config['wam_api']['resource_uri']['add_user'];
        $ch = curl_init($serviceUri);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['wam_api']['gebruiker'] . ":" . $this->config['wam_api']['wachtwoord']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        return $result;
    }

    public function updateUsers($users) {
        //url-ify de data voor de POST
        $dataString = json_encode($users);

        //open connection
        $ch = curl_init();

        //de url van de bestemming , aantal POST variabelen en POST data wordt ingesteld
        $serviceUri = $this->config['wam_api']['base_uri'].$this->config['wam_api']['resource_uri']['update_user'];
        curl_setopt($ch,CURLOPT_URL, $serviceUri);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['wam_api']['gebruiker'] . ":" . $this->config['wam_api']['wachtwoord']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch,CURLOPT_POSTFIELDS, $dataString);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //Onderstaande optie zorgt ervoor dat curl_exec de contents van de cURL retourneerd, inplaats van de contents te echo'en
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        // Als je content-type aanpast naar application/json dan geeft PHP 5.6 een warning ivm $HTTP_RAW_POST_DATA is deprecated en een notice over een header die al ingesteld werd.
        // Daarom werd deze header aangepast naar application/x-www-form-urlencoded.
        // meer info over het $HTTP_RAW_POST_DATA probleem in PHP 5.6 vindt je terug op deze website: https://github.com/matomo-org/matomo/issues/6465
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Content-Length: ' . strlen($dataString))
        );



        //execute post
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 0) {
            echo 'WamConnector: connection could not be established, no response from url <br />';
        }
        if ($httpcode == 201) {
            echo 'WamConnector: webapi call send <br />';
        }
        if ($httpcode == 401) {
            echo 'WamConnector: Unauthorized, either wrong credentials or access denied <br />';
        }
        return $result;

    }
}