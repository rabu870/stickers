<?php

require_once "./backend/connection.php";
    //remember the password needs to be hashed when adding to db


    $password = $db->query("SELECT `staff_password` FROM `meta` WHERE true")->fetch_row()[0];

    if (empty($_COOKIE['staff_pwd']) || $_COOKIE['staff_pwd'] !== $password) {
        header('Location: slogin.php');
        exit;
    }
?>

<!DOCTYPE html>

<html>
    <head>
        <title>PSCS Stickering</title>
    </head>
    <body>
        <?php
            $students = $db->query('SELECT first_name, last_name, login_key FROM `students` WHERE true')->fetch_all(MYSQLI_ASSOC);
            foreach($students as $student) {
                echo $student['id'];
                echo '<a href="backend/functions.php?func=setcookie&cookie=' . urlencode($student['login_key']) . '">' . $student['first_name'] . ' ' . $student['last_name'] . '</a><br>';
            }
        ?>
    </body>
</html>