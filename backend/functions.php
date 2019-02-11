<?php
function sqlize($value)
{
    global $db;
    return "\"" . mysqli_real_escape_string($db, $value) . "\"";
}
