<?php

    require_once "./backend/connection.php";
    //remember the password needs to be hashed when adding to db

    $password = $db->query("SELECT `staff_password` FROM `meta` WHERE true")->fetch_row()[0];


    /* Redirects here after login */
    $redirect_after_login = 'staff.php';

    /* Will not ask password again for */
    $remember_password = strtotime('+5 days'); // 30 days

    if (isset($_POST['password']) && crypt($_POST['password'], "P9") == $password) {
        setcookie("staff_pwd", $password, $remember_password);
        header('Location: ' . $redirect_after_login);
        exit;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password protected</title>
</head>
<body>
    <div style="text-align:center;margin-top:50px;">
    Enter the staff password to view this page:
        <form method="POST">
            <input type="password" name="password">
        </form>
    </div>
</body>
</html>