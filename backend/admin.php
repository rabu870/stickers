<?php
    require_once './verify.php';
    require_once './connection.php';
    if($access == 1) {
        if($_GET['func'] == 'load') {
            $s = $db->query('SELECT * FROM students')->fetch_all(MYSQLI_ASSOC);
            $a = $db->query('SELECT * FROM `admin`')->fetch_all(MYSQLI_ASSOC);
            $c = $db->query('SELECT * FROM `meta`')->fetch_all(MYSQLI_ASSOC);
            echo "[" . json_encode($s, JSON_PRETTY_PRINT) . ", " . json_encode($a, JSON_PRETTY_PRINT) . ", " . json_encode($c, JSON_PRETTY_PRINT) . "]";
        }elseif($_GET['func'] == 'students'){
            $students = json_decode($_GET['students'],$assoc = true);
            $db->query("DELETE FROM `students` WHERE true");
            foreach($students as $student){
                if($student['id']){
                    if($student['loginKey'] || !$student['loginKey'] == ''){
                        $key = mysqli_real_escape_string($db,$student['loginKey']);
                    }else{
                        $key = 0;
                    }
                    $query = "INSERT INTO `students`(`id`, `first_name`, `last_name`, `email`, `grad_year`, `login_key`) VALUES (\"".mysqli_real_escape_string($db,$student['id'])."\",\"".mysqli_real_escape_string($db,$student['firstName'])."\",\"".mysqli_real_escape_string($db,$student['lastName'])."\",\"".mysqli_real_escape_string($db,$student['email'])."\",\"".mysqli_real_escape_string($db,$student['gradYear'])."\",\"".$key."\")";
                }else{
                    $query = "INSERT INTO `students`(`first_name`, `last_name`, `email`, `grad_year`) VALUES (\"".mysqli_real_escape_string($db,$student['firstName'])."\",\"".mysqli_real_escape_string($db,$student['lastName'])."\",\"".mysqli_real_escape_string($db,$student['email'])."\",".mysqli_real_escape_string($db,$student['gradYear']).")";
                }
                $db->query($query);
            }
        } elseif($_GET['func'] == 'admins'){
            $admins = json_decode($_GET['admins'],$assoc = true);
            $db->query("DELETE FROM `admin` WHERE true");
            foreach($admins as $admin){
                if($admin['id']){
                    if($admin['loginKey'] || !$admin['loginKey'] == ''){
                        $key = mysqli_real_escape_string($db,$admin['loginKey']);
                    }else{
                        $key = 0;
                    }
                    $query = "INSERT INTO `admin`(`id`, `first_name`, `last_name`, `email`, `login_key`) VALUES (\"".mysqli_real_escape_string($db,$admin['id'])."\",\"".mysqli_real_escape_string($db,$admin['firstName'])."\",\"".mysqli_real_escape_string($db,$admin['lastName'])."\",\"".mysqli_real_escape_string($db,$admin['email'])."\",\"".$key."\")";
                }else{
                    $query = "INSERT INTO `admin`(`first_name`, `last_name`, `email`) VALUES (\"".mysqli_real_escape_string($db,$admin['firstName'])."\",\"".mysqli_real_escape_string($db,$admin['lastName'])."\",\"".mysqli_real_escape_string($db,$admin['email'])."\")";
                }
                $db->query($query);
            }
        } elseif($_GET['func'] == 'meta') {
            $meta = json_decode($_GET['meta'],$assoc = true);
            $db->query("DELETE FROM `meta` WHERE true");
            $query = "INSERT INTO `meta`(`stickering_active`, `blacks_allotted` , `greys_allotted` , `blacks_allotted_block` , `greys_allotted_block`) VALUES (\"".mysqli_real_escape_string($db,$meta['stickeringActive'])."\",\"".mysqli_real_escape_string($db,$meta['blacksAllotted'])."\" ,\"".mysqli_real_escape_string($db,$meta['greysAllotted'])."\" ,\"".mysqli_real_escape_string($db,$meta['blacksAllottedBlock'])."\" ,\"".mysqli_real_escape_string($db,$meta['greysAllottedBlock'])."\")";
            $db->query($query);
        }
    } else {
        die();
    }
?>