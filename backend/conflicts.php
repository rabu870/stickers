<?php
require_once './connection.php';
require_once './functions.php';

$classes = $db->query("SELECT * FROM classes WHERE true");