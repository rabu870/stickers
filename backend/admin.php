<?php
    require_once './verify.php';
    require_once './connection.php';
    if($access == 1) {
        if($_GET['func'] == 'load') {
            $result = $db->query('SELECT id, first_name, last_name, email, grad_year FROM students');
            echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
        }   
    } else {
        die();
    }
    
?>