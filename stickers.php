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
        $stickers = $db->query('SELECT * FROM `stickers` WHERE true ORDER BY `priority` ASC')->fetch_all($resulttype = MYSQLI_ASSOC);
        $classes = $db->query('SELECT * FROM `classes` WHERE true')->fetch_all($resulttype = MYSQLI_ASSOC);
    }else{
        $stickers = $db->query('SELECT * FROM `stickers` WHERE `class_id` = "' . $_GET['class'] . '"')->fetch_all($resulttype = MYSQLI_ASSOC);
        array_push($classes, $db->query('SELECT * FROM `classes` WHERE `id` = "' . $_GET['class'] . '"')->fetch_all($resulttype = MYSQLI_ASSOC)[0]);
    }
    
    function printClass($class, $class_info, $class_stickers) {
        global $students;
        
        $html = "<div class='class-display card' style='border: none;'><div class='card-header'>";
        $html .= "<div class='class-name card-title h1'>" . $class_info['class_name'] . '</div>';
        $html .= "<div class='class-facil card-subtitle h3'>" . $class_info['facilitator'] . '</div><div>';
        $html .= $class_info['tags'] == 'hs-only' ? "<span class='label label-primary' style='font-size:1rem'>HS Only</span>" : "";
        $html .= $class_info['tags'] == 'ms-only' ? "<span class='label label-primary' style='font-size:1rem'>MS Only</span>" : "";
        $html .= $class_info['is_mega'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Mega</span>" : "";
        $html .= $class_info['is_block'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Block</span>" : "";
        $html .= "</div></div>";
        $html .= "<div class='card-body columns'>";
        $html .= "<div class='sticker-column column'>";
        $previouspriority = 1;
        foreach($class_stickers as $sticker){
            while($sticker['priority'] > $previouspriority){
                $html .= "</div><div class='sticker-column column'>";
                $previouspriority++;
            }
            $html .= "<span class='sticker label menu'>" . $students[$sticker['student_id']]['first_name'] . " " . $students[$sticker['student_id']]['last_name'][0] . ".</span>";
        }while($previouspriority < 3){
            $html .= "</div><div class='sticker-column column'>";
            $previouspriority++;
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

        <script src="./assets/scripts/libraries/jquery.min.js"></script>

        <style>
        
        @media print {

            @page {size: A4 landscape; }
        }
        
        </style>

        <script>
        
        function init() {
            alert('This page has been tested only in chrome and likely won\'t work in other browsers!\n Additionally, if you\'d like to download the stickers for later printing, you can select the "Save as PDF" option in the Chrome print dialog.');
            window.print();
        }
        
        </script>
        
    </head>

    <body style='-webkit-print-color-adjust: exact !important;' onload='init();'>
        <?php
            global $classes;
            global $stickers;
            if(empty($_GET['class'])){
                foreach($classes as $c) {
                    printClass($c['id'], $c, array_filter($stickers, function ($s) {
                        global $c;
                        return $s['class_id'] == $c['id'];
                    }));
                }
            }else{
                printClass($_GET['class'],$classes[0],$stickers);
            }
        ?>
    </body>

</html>