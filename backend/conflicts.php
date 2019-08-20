<?php

require_once './connection.php';

if ($_GET['func'] == 'load') {
    $classes = [2301,2302,2303];
    $q = "SELECT * FROM `stickers` WHERE `class_id` = ";
    $students = [];
    for ($i = 0; $i < count($classes); $i++) {
        if($i !== count($classes) -1) {
            $q = $q . $classes[$i] . " OR `class_id` = ";
        } else {
            $q .= $classes[$i];
        }
    }
    echo "[" . json_encode($db->query($q)->fetch_all()) . ', ' . json_encode($db->query("SELECT * FROM `classes` WHERE true")->fetch_all()) . ', ' . json_encode($db->query("SELECT `id`, `first_name`, `last_name` FROM `students` WHERE true")->fetch_all()) . ']';
}