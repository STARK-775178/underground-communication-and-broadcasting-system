<?php

/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/8/23
 * Time: 13:03
 */

namespace app\admin\utils;

class ApiToken {
    private $accessTokenUrl = 'http://192.168.1.4:80/admin/api/api/token';
    private $clientId = 'ac98f618b36bf237bb13b733078d2d8697903434d1e3112510d2b6d063745068';
    private $clientSecret = '0560836fc095ac70f1a08dccb472aafb';

    public function __construct() {

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