<?php


$result = $connect->query("SELECT COUNT(`id`) FROM `rev` WHERE 1");
$rw = mysqli_fetch_array($result);
$cn = $rw[0];

if(empty($chatId)){
    $result1 = $connect->query("SELECT  `region`, `city` FROM `user_tg` WHERE `chatid` = $chat_id_in");
     $rw = mysqli_fetch_array($result1);
     $regid = $rw[0];
     $cityid = $rw[1];
     if(empty($cityid)){
        $result1 = $connect->query("SELECT `name` FROM `regions` WHERE  id = $regid");
        $rw = mysqli_fetch_array($result1);
        $namecity = $rw[0];
    }else{
        $result1 = $connect->query("SELECT  `name`  FROM `city` WHERE id = $cityid");
        $rw = mysqli_fetch_array($result1);
        $namecity = $rw[0];
        
    }
}else{
    $result1 = $connect->query("SELECT  `region`, `city` FROM `user_tg` WHERE `chatid` = $chatId");
    $rw = mysqli_fetch_array($result1);
    $regid = $rw[0];
    $cityid = $rw[1];
    if(empty($cityid)){
        $result1 = $connect->query("SELECT `name` FROM `regions` WHERE  id = $regid");
        $rw = mysqli_fetch_array($result1);
        $namecity = $rw[0];
    }else{
        $result1 = $connect->query("SELECT  `name`  FROM `city` WHERE id = $cityid");
        $rw = mysqli_fetch_array($result1);
        $namecity = $rw[0];
        
    }
}


$key1 = 'Товары('.$namecity.')';
$key2 = 'Локации';
$key3 = 'Профиль';
$key4 = 'Поддержка';
$key5 = 'Отзывы ('.$cn.')';
$key6 = 'Заработать';




$keyboard = array(array("$key1","$key2","$key3"),array("$key4","$key5","$key6"));
$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => false);
$reply = json_encode($resp);