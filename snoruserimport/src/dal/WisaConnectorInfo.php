<?php
/**
 * Created by PhpStorm.
 * User: cob
 * Date: 13-06-18
 * Time: 13:15
 */

namespace Snor\UserImport\Dal;


class WisaConnectorInfo
{
    private $requestUri;
    private $username;
    private $password;

    /**
     * WisaConnectorInfo constructor.
     * @param $requestUri
     * @param $username
     * @param $password
     */
    public function __construct($requestUri, $username, $password)
    {
        $this->requestUri = $requestUri;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }


}