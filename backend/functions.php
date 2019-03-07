<?php
function sqlize($value)
{
    global $db;
    return "\"" . mysqli_real_escape_string($db, $value) . "\"";
}

function utf8ize($d)
{
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_object($d)) {
        foreach ($d as $k => $v) {
            $d->$k = utf8ize($v);
        }
    } else {
        return utf8_encode($d);
    }

    return $d;
}
function makepdf(){
    require_once __DIR__ . '/vendor/autoload.php';
    
}
