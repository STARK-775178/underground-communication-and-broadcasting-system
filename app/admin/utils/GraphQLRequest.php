<?php
/**
 * Created by PhpStorm.
 * User: 冯钰卓
 * Date: 2023/8/23
 * Time: 13:46
 */

namespace app\admin\utils;
use app\admin\utils\ApiToken;

class GraphQLRequest
{
    private $gqlUrl = 'http://192.168.1.4:80/admin/api/api/gql';
    private $token;

    public function __construct()
    {
        $apiToken = new ApiToken();
        $this->token = $apiToken->getToken();
    }

    public function sendQuery($query)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->gqlUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('GraphQL request failed: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}