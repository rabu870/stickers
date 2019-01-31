<?php
    require_once './verify.php';
    require_once './connection.php';
    if($access == 1) {
        if($_GET['func'] == 'load') {
            $s = $db->query('SELECT * FROM students')->fetch_all(MYSQLI_ASSOC);
            $a = $db->query('SELECT * FROM `admin`')->fetch_all(MYSQLI_ASSOC);
            $c = $db->query('SELECT * FROM `meta`')->fetch_all(MYSQLI_ASSOC);
            echo "[" . json_encode($s, JSON_PRETTY_PRINT) . ", " . json_encode($a, JSON_PRETTY_PRINT) . ", " . json_encode($c, JSON_PRETTY_PRINT) . "]";
        }elseif($_GET['func'] == 'students' || $_GET['func'] == 'admin'){
            $people = json_decode($_GET[$_GET['func']],$assoc = true);
            $db->query("DELETE FROM `".$_GET['func']."` WHERE true");
            foreach($people as $person){
                if($person['id']){
                    if($person['loginKey'] && $person['loginKey'] != ''){
                        $key = $person['loginKey'];
                    }else{
                        $key = 0;
                    }
                    $query = "INSERT INTO `".$_GET['func']."`(`id`, `first_name`, `last_name`, `email`, `login_key`) VALUES (".sqlize($person['id']).",".sqlize($person['firstName']).",".sqlize($person['lastName']).",".sqlize($person['email']).",".sqlize($key).");";
                }else{
                    $query = "INSERT INTO `".$_GET['func']."`(`first_name`, `last_name`, `email`) VALUES (".sqlize($person['firstName']).",".sqlize($person['lastName']).",".sqlize($person['email']).");";
                }
                $db->query($query);
                if($_GET['func'] == 'students'){
                    $db->query("UPDATE `students` SET `grad_year` = ".sqlize($person['gradYear'])." WHERE `email` = ".sqlize($person['email']).";");
                }
            }
        }elseif($_GET['func'] == 'meta') {
            $meta = json_decode($_GET['meta'],$assoc = true);
            $db->query("DELETE FROM `meta` WHERE true");
            $query = "INSERT INTO `meta`(`stickering_active`, `blacks_allotted` , `greys_allotted` , `blacks_allotted_block` , `greys_allotted_block`) VALUES (\"".mysqli_real_escape_string($db,$meta['stickeringActive'])."\",\"".mysqli_real_escape_string($db,$meta['blacksAllotted'])."\" ,\"".mysqli_real_escape_string($db,$meta['greysAllotted'])."\" ,\"".mysqli_real_escape_string($db,$meta['blacksAllottedBlock'])."\" ,\"".mysqli_real_escape_string($db,$meta['greysAllottedBlock'])."\")";
            $db->query($query);
        }
    } else {
        die();
    }
    function sqlize($value) {
        include './connection.php';;
        return "\"".mysqli_real_escape_string($db,$value)."\"";
    }
?>