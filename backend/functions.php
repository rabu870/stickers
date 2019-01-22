<?php

    // Printing JSON function
    $result = $db->query('SELECT id, first_name, last_name, email, grad_year FROM students');
    echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
?>
