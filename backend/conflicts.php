<?php
require_once './connection.php';
require_once './functions.php';

$classes = $db->query("SELECT class_name, id FROM classes WHERE true")->fetch_all();


for($i=0; $i < count($classes); $i++){
    print_r($classes[$i][0]);
    echo("<br>");
}

json_encode($classes, JSON_PRETTY_PRINT);

/*
$classes = json_decode($classes);
echo($classes);*/