<?php

require_once './connection.php';

if ($_GET['func'] == 'load') {
    $students = [];
    $keys = $db->query('SELECT `id` FROM `classes` WHERE true')->fetch_all();
    foreach ($keys as $id) {
        array_push($students, [$id[0], $db->query("SELECT `student_id` FROM `stickers` WHERE `class_id` = " . $id[0])->fetch_all()]);
    }
    echo "[" . json_encode($students) . ', ' . json_encode($db->query("SELECT * FROM `classes` WHERE true")->fetch_all()) . ', ' . json_encode($db->query("SELECT `id`, `first_name`, `last_name` FROM `students` WHERE true")->fetch_all()) . ']';
}