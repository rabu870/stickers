<?php
require_once '../backend/connection.php';

$key = $_COOKIE["login_key"];
$guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
$client->setHttpClient($guzzleClient);

$payload = $client->verifyIdToken($id_token);

if(db->query("SELECT id FROM `students` WHERE `login_key` = ".$key)->num_rows()){
    echo "2";
}elseif(db->query("SELECT id FROM `admin` WHERE `login_key` = ".$key)->num_rows()){
    echo "1";
}else{
    echo "0";
}
?>