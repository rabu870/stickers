<?php

$key = $_COOKIE["login_key"];
if($db->query("SELECT id FROM `students` WHERE `login_key` = ".$key)->num_rows){
    $access = 2;
}elseif($db->query("SELECT id FROM `admin` WHERE `login_key` = ".$key)->num_rows){
    $access = 1;
}else{
    setcookie('login_key', '', -100, '/');
    die();
}
?>