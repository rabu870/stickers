<?php
require_once './verify.php';
require_once './connection.php';
if ($access == 2) {
    if ($_GET['func'] == 'load') {

    }
} else {
    die();
}
function sqlize($value)
{
    global $db;
    return "\"" . mysqli_real_escape_string($db, $value) . "\"";
}
