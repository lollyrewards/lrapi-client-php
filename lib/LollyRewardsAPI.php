<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================

set_include_path(dirname(__FILE__). '/../vendor/phpseclib0.3.6');

require_once('Crypt/RSA.php');

require_once('signjson.php');
require_once('util.php');

require_once('HttpEndpoint.php');

class LollyRewardsAPI
{
    public $from_address;
    public $private_key;
    public $pubkey_uri;

    public function __construct($actor_uri, $private_key_file_or_str, $pubkey_uri = NULL,
                                $backend_host = "https://www.lollyrewards.com/api",
                                $http_auth_username = NULL, $http_auth_password = NULL)
    {
        $this->from_address = $actor_uri;
        $this->pubkey_uri = $pubkey_uri;
        $this->private_key = file_exists($private_key_file_or_str) ?
            file_get_contents($private_key_file_or_str) : $private_key_file_or_str;
        $this->http = new HttpEndpoint($backend_host, $http_auth_username, $http_auth_password);
    }

    public function register_pk($display_name = NULL, $revoke_old_keys = false)
    {
        $rsa = new Crypt_RSA();
        $rsa->loadKey($this->private_key);
        $pk_arr=$rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);

        $jRegister = array(
            'pubkey_n' => $pk_arr['n']->toString(),
            'pubkey_e' => $pk_arr['e']->toString(),
            'agree' => '1',
            'clver' => '2',
            'from_address' => "mailto:" . $this->from_address,
            'ts' => millitime()
        );
        if ($display_name) {
            $jRegister['display_name'] = $display_name;
        }
        if ($revoke_old_keys) {
            $jRegister["disable_prev_keys"] = $revoke_old_keys;
        }
        $jRegister['sig_sha512_rsa'] = signJson($this->private_key, $jRegister);
        echo "Sending \n";
        echo str_replace("\"", "\\\"", json_encode($jRegister, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) . "\n";
        return $this->http->post('/register', $jRegister);
    }

    public function confirm_pk($confirm_code) {
        $jConfirm = array(
            "from_address" => $this->pubkey_uri,
            "nonce" => $confirm_code,
            "ts" => millitime());
        $jConfirm['sig_sha512_rsa'] = signJson($this->private_key, $jConfirm);
        return $this->http->post('/register/confirm', $jConfirm);
    }

}
