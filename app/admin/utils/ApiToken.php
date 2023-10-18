<?php

/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/8/23
 * Time: 13:03
 */

namespace app\admin\utils;

class ApiToken {

    private $accessTokenUrl;
    private $clientId;
    private $clientSecret;

    public function __construct() {
        $config = require 'config/apiConfig.php';
        $this->accessTokenUrl = $config['accessTokenUrl'];
        $this->aclientId = $config['clientId'];
        $this->clientSecret = $config['clientSecret'];
    }

    public function getToken() {
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );

        $context = stream_context_create($options);
        $response = file_get_contents($this->accessTokenUrl, false, $context);
        $result = json_decode($response, true);

        if ($result && isset($result['access_token'])) {
            return $result['access_token'];
        } else {
            return null;
        }
    }
}