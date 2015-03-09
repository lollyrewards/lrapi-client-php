<?php

//=======================================================================
// Copyright Lolly Rewards Ltd 2015.
// Distributed under the MIT License.
// (See accompanying file LICENSE or copy at
//  http://opensource.org/licenses/MIT)
//=======================================================================


function signJson($private_key, $jData)
{
    $pkeyid = openssl_pkey_get_private($private_key);
    // !! important, canonical order
    ksort($jData);
    // compute signature with SHA-512
    $canonical = json_encode($jData, JSON_UNESCAPED_SLASHES);
    openssl_sign($canonical, $signature, $pkeyid, "sha512");
    return base64_encode($signature);
}