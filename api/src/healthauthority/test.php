<?php
$clientKeyPair = sodium_crypto_kx_keypair();
echo base64_encode($clientKeyPair);
die;
$clientPublicKey = sodium_crypto_kx_publickey($clientKeyPair);

$haKeyPair = sodium_crypto_kx_keypair();
$haPublicKey = sodium_crypto_kx_publickey($haKeyPair);

$clientSessionKeys = sodium_crypto_kx_client_session_keys($clientKeyPair, $haPublicKey);
$haSessionKeys = sodium_crypto_kx_server_session_keys($haKeyPair, $clientPublicKey);

var_dump($clientSessionKeys);
var_dump($haSessionKeys);

echo base64_encode($clientSessionKeys) . "\n";
echo base64_encode($haSessionKeys) . "\n";

echo ($clientSessionKeys == $haSessionKeys ? "" : "NOT ") . "EQUAL\n";
echo "\n";
