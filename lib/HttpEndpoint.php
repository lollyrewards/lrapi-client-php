<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================

class HttpEndpoint {

    public $host;

    public $http_auth_username;
    public $http_auth_password;

    public function __construct($host, $http_auth_username = NULL, $http_auth_password = NULL) {
        $this->host = $host;
        $this->http_auth_username = $http_auth_username;
        $this->http_auth_password = $http_auth_password;
    }

    public function post($uri, $data)
    {
        $data_string = json_encode($data, JSON_UNESCAPED_SLASHES);

        $full_uri = $this->host . $uri;

        $ch = curl_init($full_uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        if ($this->http_auth_username && $this->http_auth_password) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->http_auth_username}:{$this->http_auth_password}");
        }

        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    public function get($uri, $data)
    {
        $data_string = http_build_query($data);

        $full_uri = $this->host . $uri . '?' . $data_string;

        $ch = curl_init($full_uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($this->http_auth_username && $this->http_auth_password) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->http_auth_username}:{$this->http_auth_password}");
        }

        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }

        return $result;
    }

}