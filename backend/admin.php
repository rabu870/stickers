<?php
require_once './verify.php';
require_once './connection.php';
require_once './functions.php';
if ($access == 1) {
    if ($_GET['func'] == 'load') {
        $s = $db->query('SELECT * FROM `students`')->fetch_all(MYSQLI_ASSOC);
        $a = $db->query('SELECT * FROM `admin`')->fetch_all(MYSQLI_ASSOC);
        $c = $db->query('SELECT * FROM `meta`')->fetch_all(MYSQLI_ASSOC);
        $u;
        foreach($s as $stu){
            if(!$stu['stickered']){
                array_push($u,$stu);
            }
        }
        echo "[" . json_encode($s, JSON_PRETTY_PRINT) . ", " . json_encode($a, JSON_PRETTY_PRINT) . ", " . json_encode($c, JSON_PRETTY_PRINT) . ", " . json_encode($u, JSON_PRETTY_PRINT) . "]";
    } elseif ($_GET['func'] == 'students' || $_GET['func'] == 'admin') {
        $people = json_decode($_GET[$_GET['func']], $assoc = true);
        $db->query("DELETE FROM `" . $_GET['func'] . "` WHERE true");
        foreach ($people as $person) {
            if ($person['id']) {
                if ($person['loginKey'] && $person['loginKey'] != '') {
                    $key = $person['loginKey'];
                } else {
                    $key = 0;
                }
                $query = "INSERT INTO `" . $_GET['func'] . "`(`id`, `first_name`, `last_name`, `email`, `login_key`) VALUES (" . sqlize($person['id']) . "," . sqlize($person['firstName']) . "," . sqlize($person['lastName']) . "," . sqlize($person['email']) . "," . sqlize($key) . ");";
            } else {
                $query = "INSERT INTO `" . $_GET['func'] . "`(`first_name`, `last_name`, `email`";
                if(!empty($person['gradYear'])){
                    $query = $query . ", `grad_year`";
                }
                $query = $query . ") VALUES (" . sqlize($person['firstName']) . "," . sqlize($person['lastName']) . "," . sqlize($person['email']);
                if(!empty($person['gradYear'])){
                    $query = $query . ", " . sqlize($person['gradYear']);
                }
                $query = $query . ");";
            }
            $db->query($query);
            if ($_GET['func'] == 'students') {
                $db->query("UPDATE `students` SET `grad_year` = " . sqlize($person['gradYear']) . " WHERE `email` = " . sqlize($person['email']) . ";");
            }
        }
    } elseif ($_GET['func'] == 'meta') {
        $meta = json_decode($_GET['meta'], $assoc = true);
        if ($meta['stickeringActive'] == '1') {
            $active = $db->query("SELECT `stickering_active` FROM `meta` WHERE true")->fetch_row();
            if ($active[0] == "0") {
                $db->query("UPDATE `students` SET `stickered` = 0 WHERE true");
                $db->query("DELETE FROM `classes` WHERE true;");
                $fac = "Michael Coffey";
                $mega = 0;
                $block = 0;
                $html = json_decode(json_encode(simplexml_load_file('https://classes.pscs.org/feed')), $assoc = true);
                $query = "INSERT into `classes` (`class_name`, `link`, `facilitator`, `is_mega`, `is_block`) VALUES (";
                foreach ($html['channel']['item'] as $class) {
                    $query = $query . sqlize($class['title']) . ", " . sqlize($class['link']) . ", " . sqlize($fac) . ", " . sqlize($mega) . ", " . sqlize($block) . ")";
                    if ($class != $html['channel']['item'][count($html['channel']['item']) - 1]) {
                        $query = $query . ",(";
                    } else {
                        $query = $query . ';';
                    }
                }
                $db->query($query);

            } else {
                die();
            }

        }
        $db->query("DELETE FROM `meta` WHERE true");
        $query = "INSERT INTO `meta`(`stickering_active`, `blacks_allotted` , `greys_allotted` , `blacks_allotted_block` , `greys_allotted_block`) VALUES (" . sqlize($meta['stickeringActive']) . "," . sqlize($meta['greysAllotted']) . " ," . sqlize($meta['blacksAllotted']) . " ," . sqlize($meta['blacksAllottedBlock']) . " ," . sqlize($meta['greysAllottedBlock']) . ")";
        $db->query($query);
    }
} else {
    die();
}
