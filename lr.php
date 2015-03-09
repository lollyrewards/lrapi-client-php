#!/usr/bin/env php
<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================

require_once("lib/LollyRewardsAPI.php");
require_once("lib/util.php");

$COMMANDS = array("register", "confirm");

function print_help()
{
    global $argv;
    echo "Usage: \n";
    echo " -- Create account / Associate a new public key with an email address\n";
    echo "      " . $argv[0] . " {email_address} register [display_name] (optional)\n";
    echo " -- Create account / Register a new public key with an account\n";
    echo "      " . $argv[0] . " {email_address} confirm {nonce}\n";
}

if (($argc == 1) || ($argv[1] == "help")) {
    print_help();
    exit;
}

$from_account = $argv[1];
$ACCOUNTS = ld_json_from(".accounts");

$private_key_file = $from_account . ".key";

if (!file_exists($private_key_file)) {
    echo "[ERROR]: Missing private key file $private_key_file\n";
    exit(-1);
}

if ($argc < 3) {
    echo "[ERROR]: Missing command\n";
    print_help();
    exit(-1);
}
$cmd = $argv[2];

if (!in_array($cmd, $COMMANDS)) {
    echo "Unknown command: '$cmd'\n";
    echo "Supported commands are '" . join("', '", $COMMANDS) . "'\n";
    exit(-1);
}

switch ($cmd) {
    case "register":
        $LR = new LollyRewardsAPI($from_account, $private_key_file);
        if ($argc < 3) {
            echo "[ERROR]: Bad command\n";
            print_help();
            exit(-1);
        }
        try {
            $resp = $LR->register_pk(isset($argv[3]) ? $argv[3] : NULL, true);
            $resp_arr = json_decode($resp,true);
            if (isset($resp_arr["pubkey_uri"])) {
                write_to_account_pk($from_account, $resp_arr["pubkey_uri"]);
                $resp .= "\n";
                $resp .= "Your .accounts file  has been updated.\n";
                $resp .= "A confirmation email has been sent to $from_account.\n";
                $resp .= "Any old keys associated with $from_account are now revoked.\n";
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            exit(-1);
        }
        break;

    case "confirm":
        if (isset($ACCOUNTS[$from_account]))
            $LR = new LollyRewardsAPI($from_account, $private_key_file, $ACCOUNTS[$from_account][0]);
        else
            throw new Exception("Unknown or unregistered account: ".$from_account);
        if ($argc == 3) {
            echo "[ERROR]: Bad command\n";
            print_help();
            exit(-1);
        }
        try {
            $resp = $LR->confirm_pk($argv[3]);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(-1);
        }
        break;

    default:
        echo "[ERROR]: Unknown command: $cmd\n";
        print_help();
        exit(-1);
}

echo "Response:\n $resp\n";
