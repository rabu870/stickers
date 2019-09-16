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
        
        // $html = "<div class='class-display card' style='border: none;'><div class='card-header'>";
        // $html .= "<div class='class-name card-title h1'>" . $class_info['class_name'] . '</div>';
        // $html .= "<div class='class-facil card-subtitle h3'>" . $class_info['facilitator'] . '</div>';
        // $html .= $class_info['availability'] !== '' ? "<div class='class-availabilty card-subtitle h5'>" . $class_info['availability'] . '</div>' : '';
        // $html .= $class_info['needs'] !== '' ? "<div class='class-needs card-subtitle h5'>" . $class_info['needs'] . '</div><div>' : '<div>';
        // $html .= $class_info['tags'] == 'hs-only' ? "<span class='label label-primary' style='font-size:1rem'>HS Only</span>" : "";
        // $html .= $class_info['tags'] == 'ms-only' ? "<span class='label label-primary' style='font-size:1rem'>MS Only</span>" : "";
        // $html .= $class_info['is_mega'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Mega</span>" : "";
        // $html .= $class_info['is_block'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Block</span>" : "";
        // $html .= $class_info['mature'] == '1' ? "<span class='label label-primary' style='font-size:1rem'>Mature themes</span>" : "";
        // $html .= "</div></div>";
        // $html .= "<div class='card-body columns'>";
        // $html .= "<div class='sticker-column column'>";
        // $previouspriority = 1;
        // foreach($class_stickers as $sticker){
        //     while($sticker['priority'] > $previouspriority){
        //         $html .= "</div><div class='sticker-column column'>";
        //         $previouspriority++;
        //     }
        //     $html .= "<span class='sticker label menu'>" . $students[$sticker['student_id']]['first_name'] . " " . $students[$sticker['student_id']]['last_name'][0] . ".</span>";
        // }while($previouspriority < 3){
        //     $html .= "</div><div class='sticker-column column'>";
        //     $previouspriority++;
        // }
        // $html .= "</div></div></div>";

        $html = '{';
        $html .= '"className": "' . $class_info['class_name'] . '",';
        $html .= '"facilitator": "' . $class_info['facilitator'] . '",';
        $html .= '"isMega": "' . $class_info['is_mega'] . '",';
        $html .= '"isBlock": "' . $class_info['is_block'] . '",';
        $html .= '"availability": "' . $class_info['availability'] . '",';
        $html .= '"stickers": [';
        foreach($class_stickers as $key=>$sticker) {
            $html .= '{"student": "' . $students[$sticker['student_id']]['first_name'] . " " . $students[$sticker['student_id']]['last_name'] . '",';
            $html .= '"priority": "' . $sticker['priority'] . '"';
            $html .= '}';
        }
        $html .= ']}';

        print_r($html);
        //print_r($class_stickers);
    }

?>

<pre>
        <?php
            global $classes;
            global $stickers;
            if(empty($_GET['class'])){
                echo '[';
                foreach($classes as $key=>$c) {
                    printClass($c['id'], $c, array_filter($stickers, function ($s) {
                        global $c;
                        return $s['class_id'] == $c['id'];
                    }));
                    if($key + 1 <count($classes)) {
                        echo ',';
                    }
                }
                echo ']';
            }//else{
            //     echo '[';
            //     printClass($_GET['class'],$classes[0],$stickers);
            //     echo ']';
            // }
        ?>
        </pre>