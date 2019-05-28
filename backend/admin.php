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
    } elseif ($_POST['func'] == 'students' || $_POST['func'] == 'admin') {
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
                $query = "INSERT INTO `" . $_POST['func'] . "`(`first_name`, `last_name`, `email`";
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
            if ($_POST['func'] == 'students') {
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
                $db->query("DELETE FROM `stickers` WHERE true;");
                $data = file_get_contents('https://classes.pscs.org/feed');
                $data = str_replace("content:encoded","content",$data);
                $xml = simplexml_load_string($data, null, LIBXML_NOCDATA);
                $html = json_decode(json_encode($xml), $assoc = true);
                $query = "INSERT into `classes` (`class_name`, `link`, `facilitator`, `is_mega`, `is_block`, `tags`) VALUES (";
                foreach ($html['channel']['item'] as $class) {
                    // disgusting but it was the only way i could get it to work... strips out everything outside the facilitator
                    $fac = substr((string) $class['content'], strrpos((string) $class['content'],'<p class="facilitator-name">') + 28, -4);
                    $mega = in_array('mega', $class['category']) ? 1 : 0;
                    $block = in_array('block', $class['category']) ? 1 : 0;
                    $tags = '';
                    if(in_array('hs-only', $class['category'])) {
                        $tags = 'hs-only';
                    } elseif(in_array('ms-only', $class['category'])) {
                        $tags = 'ms-only';
                    }
                    $query = $query . sqlize($class['title']) . ", " . sqlize($class['link']) . ", " . sqlize($fac) . ", " . sqlize($mega) . ", " . sqlize($block) . ", " . sqlize($tags) . ")";
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
    } elseif($_GET['func'] == 'reminder') {
        $mail = new PHPMailer;
        $mail->isSMTP();
        //will eventually be something like noreply-stickers@pscs.org
        $mail->setFrom('no_reply_stickering@pscs.org', 'PSCS Stickers');
        $mail->addAddress('no_reply_stickers@pscs.org', 'PSCS Stickers');
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
        $mail->Body = 'You haven\'t yet stickered! Please do so as soon as possible.';
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
