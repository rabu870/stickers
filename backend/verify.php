<?php
$key = $_COOKIE["login_key"];
if(db->query("SELECT id FROM `students` WHERE `login_key` = ".$key.";")->num_rows){
    echo "2";
}elseif(db->query("SELECT id FROM `admin` WHERE `login_key` = ".$key)->num_rows){
    echo "1";
}else{
    echo "0";
}
?>