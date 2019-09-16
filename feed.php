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

        $html = '{';
        $html .= '"className": "' . $class_info['class_name'] . '",';
        $html .= '"facilitator": "' . $class_info['facilitator'] . '",';
        $html .= '"isMega": "' . $class_info['is_mega'] . '",';
        $html .= '"isBlock": "' . $class_info['is_block'] . '",';
        $html .= '"availability": "' . $class_info['availability'] . '",';
        $html .= '"stickers": [';
        for($i = 0; $i < count($class_stickers); $i++) {
            $sticker = array_values($class_stickers)[$i];
            $html .= '{"student": "' . $students[$sticker['student_id']]['first_name'] . " " . $students[$sticker['student_id']]['last_name'] . '",';
            $html .= '"priority": "' . $sticker['priority'] . '"';
            $html .= '}';
            if($i + 1 <count($class_stickers)) {
                $html .= ',';
            }
            
        }
        $html .= ']}';

        print_r($html);
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
            }
        ?>
        </pre>