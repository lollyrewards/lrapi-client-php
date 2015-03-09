<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================

$ROOTDIR = dirname(__FILE__)."/..";

function gen_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}


function millitime()
{
    $microtime = microtime();
    $comps = explode(' ', $microtime);

    // Note: Using a string here to prevent loss of precision
    // in case of "overflow" (PHP converts it to a double)
    return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}

function lookup_by_key_or_ret(array $kv, $val) {
    return isset($kv[$val]) ? $kv[$val] : $val;
}

function lookup_by_val_or_ret(array $kv,$val) {
    return lookup_by_key_or_ret(array_flip($kv), $val);
}


function ld_json_from($json_file) {
    return file_exists($json_file) ?
        json_decode(file_get_contents($json_file),true) :
        array();
}

function pk_uri_for_account_uri($acc_uri) {
    global $ROOTDIR;
    $ACCOUNTS = ld_json_from("$ROOTDIR/.accounts");
    return isset($ACCOUNTS[$acc_uri]) ? $ACCOUNTS[$acc_uri] : NULL;
}

function write_to_account_pk($acc_uri,$pk_uri) {
    global $ROOTDIR;
    $ACCOUNTS = ld_json_from("$ROOTDIR/.accounts");
    if (isset($ACCOUNTS[$acc_uri]))
        array_unshift($ACCOUNTS[$acc_uri],$pk_uri);
    else
        $ACCOUNTS[$acc_uri] = array($pk_uri);
    file_put_contents("$ROOTDIR/.accounts", json_encode($ACCOUNTS, JSON_UNESCAPED_SLASHES));
}