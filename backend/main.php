<?php
require_once './verify.php';
require_once './connection.php';
require_once './functions.php';
if ($access == 2) {
    if ($db->query("SELECT stickering_active FROM meta WHERE true;")->fetch_row()[0] == '1') {
        $allotted = $db->query("SELECT `blacks_allotted`, `greys_allotted`, `blacks_allotted_block`, `greys_allotted_block` FROM `meta` WHERE true;")->fetch_row();
        $id = $db->query("SELECT `id` FROM `students` WHERE `login_key` = " . sqlize($_COOKIE["login_key"]))->fetch_row()[0];
        if ($_GET['func'] == 'load') {
            $stickers = [];
            for ($i = 1; $i < 4; $i++) {
                array_push($stickers, $db->query("SELECT `class_id` FROM `stickers` WHERE `student_id` = " . $id . " AND `priority` = " . $i . " AND `is_block` = false")->fetch_all());
            }
            for ($i = 1; $i < 4; $i++) {
                array_push($stickers, $db->query("SELECT `class_id` FROM `stickers` WHERE `student_id` = " . $id . " AND `priority` = " . $i . " AND `is_block` = true")->fetch_all());
            }
            $classes = $db->query("SELECT * FROM `classes` WHERE true")->fetch_all();
            $hs = $db->query("SELECT `hs` FROM `students` WHERE `id` = " . $id)->fetch_row()[0];
            echo "[" . json_encode($stickers, JSON_PRETTY_PRINT) . ", " . json_encode($classes, JSON_PRETTY_PRINT) . ", " . json_encode(utf8ize($allotted), JSON_UNESCAPED_UNICODE) . ", " . json_encode(utf8ize($hs), JSON_PRETTY_PRINT) . ", " . json_encode(utf8ize($db->query('SELECT first_name FROM students WHERE id = ' . $id)->fetch_row()[0]), JSON_PRETTY_PRINT) . "]";
        } elseif ($_GET['func'] == 'update') {
            $stickers = json_decode($_GET['stickers']);
            $allottedlist = array($allotted[0], $allotted[1], '', $allotted[2], $allotted[3], '');
            $db->query("DELETE FROM `stickers` WHERE `student_id` = " . $id);
            $check = false;
            foreach ($stickers as $key => $stickerlist) {
                if ($key % 3 != 2 && count($stickerlist) > $allottedlist[$key]) {
                    die('No cheating');
                } else {
                    foreach ($stickerlist as $sticker) {
                        $check = true;
                        $db->query("INSERT INTO `stickers` (`student_id`, `class_id`, `priority`, `is_block`) VALUES (" . $id . ", " . sqlize($sticker) . ", " . sqlize(($key % 3) + 1) . ", " . sqlize((int) floor($key / 3)) . ")");
                    }
                }
            }if($check){
                $db->query("UPDATE `students` SET `stickered` = 1 WHERE `id` = " . $id);
            }
        }
    } else {
        echo '0';
    }
} else {
    http_response_code(400);
    die();
}
