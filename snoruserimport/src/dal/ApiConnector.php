<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 29-05-18
 * Time: 16:48
 */

namespace Snor\UserImport\Dal;


class ApiConnector
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

    public function fetch() {
        $header = array('http' => array('header'  => "Authorization: Basic " . base64_encode($this->apiConnectionInfo->getUsername().":".$this->apiConnectionInfo->getPassword())));
        $context = stream_context_create($header);
        $this->data = file_get_contents($this->apiConnectionInfo->getRequestUri(), false, $context);
    }
}