<?php

require_once './connection.php';

if ($_GET['func'] == 'load') {
    echo "[" . json_encode($db->query("SELECT * FROM `classes` WHERE true")->fetch_all()) . ', ' . json_encode($db->query("SELECT `id`, `first_name`, `last_name` FROM `students` WHERE true")->fetch_all()) . ']';
} else if ($_GET['func'] == 'stickers') {
    $classes = json_decode($_GET['slot']);
    $q = "SELECT * FROM `stickers` WHERE `class_id` = ";
    $students = [];
    for ($i = 0; $i < count($classes); $i++) {
        if($i !== count($classes) -1) {
            $q = $q . intval($classes[$i]) . " OR `class_id` = ";
        } else {
            $q .= intval($classes[$i]);
        }
    }
    echo json_encode($db->query($q)->fetch_all());
}