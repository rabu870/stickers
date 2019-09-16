<?php

    require_once '../backend/connection.php';

    $students = $db->query('SELECT `first_name`, `last_name`, `id`, `email`, `advisor` FROM `students` WHERE true')->fetch_all($resulttype = MYSQLI_ASSOC);

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
?>