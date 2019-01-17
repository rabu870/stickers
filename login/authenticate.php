<?php

    require_once './google-api-php-client/vendor/autoload.php';
    require_once '../backend/connection.php';

    $id_token = $_POST['idtoken'];

    $client = new Google_Client(['client_id' => '418027351138-p91e0o0u9dhf1d65jdqs4ml936l1b8qi.apps.googleusercontent.com']);
    $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
    $client->setHttpClient($guzzleClient);

    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $email = $payload['email'];
        //verify student
        // if($db->query("SELECT id FROM `students` WHERE `email` = '$email'")->fetch_assoc()) {
        //     setcookie('student_login_key', $id_token, time() + (86400 * 30 * 3), "/");
        //     setcookie('admin_login_key', '', time() - 3600, '/');
        //     $db->query("UPDATE `students` SET `login_key` = '$id_token' WHERE `email` = '$email'");
        //     echo 'successful student';
        // } elseif ($db->query("SELECT id FROM `admin` WHERE `email` = '$email'")->fetch_assoc()) {
        //     setcookie('admin_login_key', $id_token, time() + (86400 * 30 * 3), "/");
        //     setcookie('student_login_key', '', time() - 3600, '/');
        //     $db->query("UPDATE `admin` SET `login_key` = '$id_token' WHERE `email` = '$email'");
        //     echo 'successful admin';
        // } else {
        //     echo 'invalid';
        // }
    } else {
        echo 'invalid';
    }

?>