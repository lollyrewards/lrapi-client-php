<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================

require_once('HttpEndpoint.php');
require_once('signjson.php');
require_once('util.php');

class LollyRewardsCouponAPI {

    public $signature_creator;
    public $private_key;
    public $http;

    public function __construct($signature_creator, $private_key_file_or_str,
                                $backend_host = 'https://www.lollyrewards.com/api',
                                $http_auth_username = NULL, $http_auth_password = NULL)
    {
        $this->signature_creator = $signature_creator;
        $this->private_key = file_exists($private_key_file_or_str) ?
            file_get_contents($private_key_file_or_str) : $private_key_file_or_str;
        $this->http = new HttpEndpoint($backend_host, $http_auth_username, $http_auth_password);
    }

    public function check_coupon_code($offer_code, $coupon_code) {
        $action = '/coupon-code/check';
        $jCheckCoupon = array(
            "action" => $action,
            "signature_creator" => $this->signature_creator,
            "ts" => millitime(),
            'offer_code' => $offer_code,
            "coupon_code" => $coupon_code
        );
        $jCheckCoupon['sig_sha512_rsa'] = signJson($this->private_key, $jCheckCoupon);
        $res = $this->http->get($action, $jCheckCoupon);
        $decoded_res = json_decode($res,true);
        return $decoded_res ? $decoded_res : $res;
    }

    public function lock_coupon_code($offer_code, $coupon_code) {
        $action = '/coupon-code/lock';
        $jLockCoupon = array(
            "kind" => $action,
            "signature_creator" => $this->signature_creator,
            "ts" => millitime(),
            "offer_code" => $offer_code,
            "coupon_code" => $coupon_code
        );
        $jLockCoupon['sig_sha512_rsa'] = signJson($this->private_key, $jLockCoupon);
        $res = $this->http->post($action, $jLockCoupon);
        $decoded_res = json_decode($res,true);
        return $decoded_res ? $decoded_res : $res;
    }
}