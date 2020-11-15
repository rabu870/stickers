<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

require_once './verify.php';
require_once './connection.php';
require_once './functions.php';
if ($access == 1) {
    if ($_GET['func'] == 'load') {
        $s = $db->query('SELECT * FROM `students`')->fetch_all(MYSQLI_ASSOC);
        $a = $db->query('SELECT * FROM `admin`')->fetch_all(MYSQLI_ASSOC);
        $c = $db->query('SELECT * FROM `meta`')->fetch_all(MYSQLI_ASSOC);
        $u = [];
        foreach($s as $stu){
            if(strval($stu['stickered']) == '0'){
                array_push($u,ucwords($stu['first_name'] . ' ' . str_split($stu['last_name'])[0] . '.'));
            }
        }
        echo "[" . json_encode($s, JSON_PRETTY_PRINT) . ", " . json_encode($a, JSON_PRETTY_PRINT) . ", " . json_encode($c, JSON_PRETTY_PRINT) . ", " . json_encode($u, JSON_PRETTY_PRINT) . "]";
    } elseif ($_POST['func'] == 'students') {
        $people = json_decode($_POST[$_POST['func']], $assoc = true);
        $db->query("DELETE FROM `" . $_POST['func'] . "` WHERE true");
        foreach ($people as $person) {
            if ($person['id']) {
                if ($person['loginKey'] && $person['loginKey'] != '') {
                    $key = $person['loginKey'];
                } else {
                    $key = 0;
                }
                $query = "INSERT INTO `" . $_POST['func'] . "`(`id`, `first_name`, `last_name`, `email`, `login_key`) VALUES (" . sqlize($person['id']) . "," . sqlize($person['firstName']) . "," . sqlize($person['lastName']) . "," . sqlize($person['email']) . "," . sqlize($key) . ");";
            } else {
                $query = "INSERT INTO `" . $_POST['func'] . "`(`first_name`, `last_name`, `email`, `hs`";
                $query = $query . ") VALUES (" . sqlize($person['firstName']) . "," . sqlize($person['lastName']) . "," . sqlize($person['email']);
                $query = $query . ", " . sqlize($person['hs']);
                $query = $query . ");";
            }
            $db->query($query);
            if ($_POST['func'] == 'students') {
                $db->query("UPDATE `students` SET `hs` = " . sqlize($person['hs']) . " WHERE `email` = " . sqlize($person['email']) . ";");
            }
        }
        foreach($db->query('SELECT * FROM `students`')->fetch_all(MYSQLI_ASSOC) as $key=>$student) {
            $db->query('UPDATE students SET login_key = ' . $key .' WHERE email = "' . $student['email'] . '";');
        }
    } elseif ($_POST['func'] == 'admin') {
        $people = json_decode($_POST['students'], $assoc = true);
        $db->query("DELETE FROM `" . $_POST['func'] . "` WHERE true");
        foreach ($people as $person) {
            if ($person['id']) {
                $query = "INSERT INTO `" . $_POST['func'] . "`(`id`, `first_name`, `last_name`, `email`) VALUES (" . sqlize($person['id']) . "," . sqlize($person['firstName']) . "," . sqlize($person['lastName']) . "," . sqlize($person['email']) . ");";
            } else {
                $query = "INSERT INTO `" . $_POST['func'] . "`(`first_name`, `last_name`, `email`";
                $query = $query . ") VALUES (" . sqlize($person['firstName']) . "," . sqlize($person['lastName']) . "," . sqlize($person['email']);
                $query = $query . ");";
            }
            echo $query;
            $db->query($query);
        }
    } elseif ($_GET['func'] == 'meta') {
        $meta = json_decode($_GET['meta'], $assoc = true);
        print_r($meta);
        if ($meta['stickeringActive'] == '1') {
            $active = $db->query("SELECT `stickering_active` FROM `meta` WHERE true")->fetch_row();
            if ($active[0] == "0") {
                $db->query("UPDATE `students` SET `stickered` = 0 WHERE true");
                $db->query("DELETE FROM `classes` WHERE true;");
                $db->query("DELETE FROM `stickers` WHERE true;");
                $data = file_get_contents('https://classes.pscs.org/feed');
                $data = str_replace("content:encoded","content",$data);
                $data = str_replace("dc:creator","creator",$data);
                $xml = simplexml_load_string($data, null, LIBXML_NOCDATA);
                $html = json_decode(json_encode($xml), $assoc = true);
                $query = "INSERT into `classes` (`class_name`, `link`, `facilitator`, `is_mega`, `is_block`, `tags`, `mature`, `availability`, `needs`) VALUES (";
                foreach ($html['channel']['item'] as $class) {
                    // disgusting but it was the only way i could get it to work...
                    $mature = substr(strstr(strstr((string) $class['content'], '<mature-themes>'), '</mature-themes>', true), 15) == 'Yes' ? 1 : 0;
                    $fac = substr(strstr(strstr((string) $class['content'], '<p class="facilitator-name"'), '</p>', true), 28) !== '' ? ucwords(substr(strstr(strstr((string) $class['content'], '<p class="facilitator-name"'), '</p>', true), 28)) : ucwords($class['creator']);
                    $availability = substr(strstr(substr((string) $class['content'], strpos((string) $class['content'], '<vol_avail>')), '</vol_avail>', true), 11);
                    $needs = substr(strstr(strstr((string) $class['content'], '<volunteer-needs>'), '</volunteer-needs>', true), 17) ? substr(strstr(strstr((string) $class['content'], '<volunteer-needs>'), '</volunteer-needs>', true), 17) : '';
                    echo $needs;
                    $mega = in_array('mega', $class['category']) ? 1 : 0;
                    //secondary category is mega, change back if block... probably should make this automatic.
                    //$block = in_array('block', $class['category']) ? 1 : 0;
                    $block = in_array('mega', $class['category']) ? 1 : 0;
                    $tags = '';
                    if(in_array('hs-only', $class['category'])) {
                        $tags = 'hs-only';
                    } elseif(in_array('ms-only', $class['category'])) {
                        $tags = 'ms-only';
                    }
                    $query = $query . sqlize($class['title']) . ", " . sqlize($class['link']) . ", " . sqlize($fac) . ", " . sqlize($mega) . ", " . sqlize($block) . ", " . sqlize($tags) . ", " . sqlize($mature) . ", " . sqlize($availability) . ", " . sqlize($needs) . ")";
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
        $ga = $meta['greysAllotted'] == '' ? 0 : $meta['greysAllotted'];
        $ba = $meta['blacksAllotted'] == '' ? 0 : $meta['blacksAllotted'];
        $bab = $meta['blacksAllottedBlock'] == '' ? 0 : $meta['blacksAllottedBlock'];
        $gab = $meta['greysAllottedBlock'] == '' ? 0 : $meta['greysAllottedBlock'];
        $sp = $meta['staffPassword'] == '' ? 0 : '"' . $meta['staffPassword'] . '"';

        echo crypt('act with', 'P9');
        $db->query("DELETE FROM `meta` WHERE true");
        $query = "INSERT INTO `meta`(`stickering_active`, `blacks_allotted` , `greys_allotted` , `blacks_allotted_block` , `greys_allotted_block`, `staff_password`) VALUES (" . $meta['stickeringActive'] . "," . $ga . " ," . $ba . " ," . $bab . " ," . $gab ." ," . $sp . ")";
        print_r($meta);
        echo $query;
        $db->query($query);
    } elseif($_GET['func'] == 'reminder') {
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->setFrom('no_reply_stickering@pscs.org', 'PSCS Stickers');
        $mail->addAddress('no_reply_stickering@pscs.org', 'PSCS Stickers');
        $students = $db->query("SELECT * FROM `students` WHERE `stickered` = 0;");
        foreach($students as $student){
            $mail->addBCC($student[email], $student[first_name] . ' ' . $student[last_name]);
        }
        //username and password stored in the connection.php file
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->Host = 'email-smtp.us-west-2.amazonaws.com';
        // The subject line of the email
        $mail->Subject = 'Stickering reminder';
        $mail->Body = 'You haven\'t yet stickered! Please do so as soon as possible by visiting https://stickers.pscs.org. Thank you!';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->isHTML(true);
        if(!$mail->send()) {
            header('HTTP/1.0 400 Bad error');
        }
    }
} else {
    die();
}
