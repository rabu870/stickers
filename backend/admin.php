<?php
    require_once './verify.php';
    require_once './connection.php';
    if($access == 1) {
        if($_GET['func'] == 'load') {
            $result = $db->query('SELECT * FROM students');
            echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
        }elseif($_GET['func'] == 'students'){
            $students = json_decode($_GET['students'],$assoc = true);
            $db->query("DELETE FROM `students` WHERE true");
            foreach($students as $student){
                if($student['id']){
                    if($student['loginKey']){
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
        }
    } else {
        die();
    }
?>