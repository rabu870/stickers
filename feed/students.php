<?php

    require_once '../backend/connection.php';

    $students = [];
    $students_indexed = [];

    $students = $db->query('SELECT `first_name`, `last_name`, `id`, `email`, `advisor` FROM `students` WHERE true')->fetch_all($resulttype = MYSQLI_ASSOC);
    foreach($students as $student){
        $students_indexed[$student['id']] = $student;
    }

    $students = $students_indexed;

    echo "[";
    foreach($students as $key=>$student) {
        echo '{"studentId": "' . $student['id'] . '",';
        echo '"firstName": "' . $student['first_name'] . '",';
        echo '"lastName": "' . $student['last_name'] . '",';
        echo '"email": "' . $student['email'] . '",';
        echo '"advisor": "' . $student['advisor'] . '"}';
        if($key + 1 < count($students)) {
            echo ',';
        }
    }
    echo "]";

    if(empty($_GET['class'])){
        echo '[';
        foreach($classes as $key=>$c) {
            printClass($c['id'], $c, array_filter($stickers, function ($s) {
                global $c;
                return $s['class_id'] == $c['id'];
            }));
            if($key + 1 <count($classes)) {
                echo ',';
            }
        }
        echo ']';
    }
?>