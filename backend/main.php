<?php
require_once './verify.php';
require_once './connection.php';
require_once './functions.php';
if ($access == 2) {
    if ($db->query("SELECT stickering_active FROM meta WHERE true;")->fetch_row()[0] == '1') {
        $allotted = $db->query("SELECT `blacks_allotted`, `greys_allotted`, `blacks_allotted_block`, `greys_allotted_block` FROM `meta` WHERE true;")->fetch_row();
        $id = sqlize($db->query("SELECT `id` FROM `students` WHERE `login_key` = " . sqlize($_COOKIE["login_key"]))->fetch_row()[0]);
        if ($_GET['func'] == 'load') {
            $stickers = [];
            for ($i = 0; $i < 6; $i++) {
                array_push($stickers, $db->query("SELECT `class_id` FROM `stickers` WHERE `student_id` = " . $id . " AND `priority` = " . sqlize(($i % 3) + 1) . " AND `is_block` = ". sqlize((int) floor($i / 3)))->fetch_all());
            }
            $classes = $db->query("SELECT * FROM `classes` WHERE true")->fetch_all();
            echo "[" . json_encode(utf8ize($stickers), JSON_UNESCAPED_UNICODE) . ", " . json_encode(utf8ize($classes), JSON_UNESCAPED_UNICODE) . ", " . json_encode(utf8ize($allotted), JSON_UNESCAPED_UNICODE) . "]";
        } elseif ($_GET['func'] == 'update') {
            $stickers = json_decode($_GET['stickers']);
            $allottedlist = array($allotted[0], $allotted[1], '', $allotted[2], $allotted[3], '');
            $stickyboiiz = [];
            for ($i = 0; $i < 6; $i++) {
                array_push($stickyboiiz, $db->query("SELECT `class_id` FROM `stickers` WHERE `student_id` = " . $id . " AND `priority` = " . sqlize(($i % 3) + 1) . " AND `is_block` = ". sqlize((int) floor($i / 3)))->fetch_all());
            }
            $db->query("DELETE FROM `stickers` WHERE `student_id` = " . $id);
            foreach ($stickers as $key => $stickerlist) {
                if ($key % 3 != 2 && count($stickerlist) > $allottedlist[$key]) {
                    $db->query("DELETE FROM `stickers` WHERE `student_id` = " . $id);
                    foreach ($stickyboiiz as $tim => $listtyboii) {
                        foreach ($listtyboii as $stickkk) {
                            $db->query("INSERT INTO `stickers` (`student_id`, `class_id`, `priority`, `is_block`) VALUES (" . $id . ", " . sqlize($stickkk) . ", " . sqlize(($tim % 3) + 1) . ", " . sqlize((int) floor($tim / 3)) . ")");
                        }
                    }

                    die();
                } else {
                    foreach ($stickerlist as $sticker) {
                        $db->query("INSERT INTO `stickers` (`student_id`, `class_id`, `priority`, `is_block`) VALUES (" . $id . ", " . sqlize($sticker) . ", " . sqlize(($key % 3) + 1) . ", " . sqlize((int) floor($key / 3)) . ")");
                    }
                }
            }
        }
    } else {
        echo '0';
    }
} else {
    die();
}
