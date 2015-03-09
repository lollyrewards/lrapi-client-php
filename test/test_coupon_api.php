<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================

require_once(dirname(__FILE__)."/../lib/LollyRewardsCouponAPI.php");
require_once(dirname(__FILE__)."/../lib/util.php");

function errHandle($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new ErrorException($msg, $errNo);
    } else {
        echo $msg;
    }
}

set_error_handler('errHandle');

//$signature_creator = "<YOUR PUBLIC KEY URI from .accounts>";
//$pk_file = '<PATH TO YOUR PRIVATE KEY FILE>';

if ($argc == 1) {
    echo "[ERROR]: MISSING your account's email\n";
    exit;
}

$email = $argv[1];
$ACCOUNTS = ld_json_from(".accounts");
if (isset($ACCOUNTS[$email][0]))
    $signature_creator = $ACCOUNTS[$email][0];
else {
    echo "[ERROR]: MISSING your pubkey_uri, check your .accounts file. Try to register a new key if you lost it.\n";
    exit;
}
$pk_file = "$email.key";
if (!file_exists($pk_file)) {
    echo "[ERROR]: Missing private key file $private_key_file\n";
    exit(-1);
}



$LRCApi = new LollyRewardsCouponAPI($signature_creator,$pk_file);

assert(array("result" => ":success",
             "coupon" => "TUNKNOWN",
             "status" => ":coupon/unknown") == $LRCApi->check_coupon_code("TESTOFFER", "TUNKNOWN"));

assert(array("result" => ":success",
             "coupon" => "TXXTAKEN",
             "status" => ":coupon/taken") == $LRCApi->check_coupon_code("TESTOFFER", "TXXTAKEN"));

assert(array("result" => ":success",
            "coupon" => "TXXTAKEN",
            "status" => ":coupon/unknown") == $LRCApi->check_coupon_code("INVALIDOFFER", "TXXTAKEN"));


assert(array("result" => ":success",
             "coupon" => "TXXAVAIL",
             "status" => ":coupon/available") == $LRCApi->check_coupon_code("TESTOFFER", "TXXAVAIL"));

assert(array("result" => ":success",
             "coupon" => "TXXAVAIL",
             "status" => ":coupon/unknown") == $LRCApi->check_coupon_code("INVALIDOFFER", "TXXAVAIL"));


assert(array("result" => ":error",
             "coupon" => "TUNKNOWN",
             "error" => ":coupon/unknown") == $LRCApi->lock_coupon_code("TESTOFFER", "TUNKNOWN"));

assert(array("result" => ":success",
             "coupon" => "TXXAVAIL",
             "status" => ":coupon/taken") == $LRCApi->lock_coupon_code("TESTOFFER", "TXXAVAIL"));

assert(array("result" => ":error",
             "coupon" => "TXXAVAIL",
             "error" => ":coupon/unknown") == $LRCApi->lock_coupon_code("INVALIDOFFER", "TXXAVAIL"));

assert(array("result" => ":error",
             "coupon" => "TXXTAKEN",
             "error" => ":coupon/already-taken") == $LRCApi->lock_coupon_code("TESTOFFER", "TXXTAKEN"));

assert(array("result" => ":error",
             "coupon" => "TXXTAKEN",
             "error" => ":coupon/unknown") == $LRCApi->lock_coupon_code("INVALIDOFFER", "TXXTAKEN"));


echo "ALL TESTS [PASS]\n";