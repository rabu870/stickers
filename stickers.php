<?php
    require_once './backend/connection.php';
    global $stickers;
    global $classes;
    global $students;
    $stickers = [];
    $classes = [];
    $students = [];
    $students_indexed = [];

    $students = $db->query('SELECT `first_name`, `last_name`, `id` FROM `students` WHERE true')->fetch_all($resulttype = MYSQLI_ASSOC);
    foreach($students as $student){
        $students_indexed[$student['id']] = $student;
    }
    $students = $students_indexed;
    if(empty($_GET['class'])){
        $stickers = $db->query('SELECT * FROM `stickers` WHERE true ORDER BY `priority` DESC')->fetch_all($resulttype = MYSQLI_ASSOC);
        $classes = $db->query('SELECT * FROM `classes` WHERE true')->fetch_all($resulttype = MYSQLI_ASSOC);
    }else{
        $stickers = $db->query('SELECT * FROM `stickers` WHERE `class_id` = "' . $_GET['class'] . '"')->fetch_all($resulttype = MYSQLI_ASSOC);
        array_push($classes, $db->query('SELECT * FROM `classes` WHERE `id` = "' . $_GET['class'] . '"')->fetch_row());
    }
    
    function printClass($class, $class_info, $class_stickers) {
        global $students;
        
        $html = "<div class='class-display card'><div class='card-header'>";
        $html .= "<div class='class-name card-title h1'>" . $class_info['class_name'] . '</div>';
        $html .= "<div class='class-facil card-subtitle h3'>" . $class_info['facilitator'] . '</div><div>';
        $html .= $class_info['tags'] == 'hs-only' ? "<span class='label label-primary' style='font-size:1rem'>HS Only</span>" : "";
        $html .= $class_info['tags'] == 'ms-only' ? "<span class='label label-primary' style='font-size:1rem'>MS Only</span>" : "";
        $html .= $class_info['is_mega'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Mega</span>" : "";
        $html .= $class_info['is_block'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Block</span>" : "";
        $html .= "</div></div>";
       
        $html .= "<div class='card-body'>";
        $previouspriority = -3;
        $html .= "<div>";
        foreach($class_stickers as $sticker){
            if($sticker['priority'] != $previouspriority){
                $previouspriority = $sticker['priority'];
                $html .= "</div><div class='sticker_column'>";
            }
            $html .= "<div class='sticker'>" . $students[$sticker['student_id']]['first_name'] . " " . $students[$sticker['student_id']]['last_name'][0] . "</div>";
        }
        $html .= "</div></div></div>";
        echo $html;
    }

?>

<!DOCTYPE html>
<html>

    <head>

        <title>PSCS Stickering - View Stickers</title>

        <link href="./assets/css/libraries/spectre.min.css" rel="stylesheet" type="text/css" />
        <link href="./assets/css/main.css" rel="stylesheet" type="text/css" />
        
    </head>

    <body onload='window.print()'>
        <?php
            global $classes;
            global $stickers;
            foreach($classes as $c) {
                printClass($c['id'], $c, array_filter($stickers, function ($s) {
                    global $c;
                    return $s['class_id'] == $c['id'];
                }));
            }
        ?>
    </body>

</html>