<?php

require 'db.php';

$content = file_get_contents("php://input");

$res = json_decode($content, TRUE);

$cn = count($res);
$i = 1;

while ($i <= $cn) {
    $rev = $res[$i]['rev'];
    $rev = str_replace(')', '', $rev);
    $mounth = mounth($res[$i]['mounth']);
    $year = $res[$i]['year'];
    $chislo = $res[$i]['chislo'];
    $fulldate = $chislo . '.' . $mounth . '.' . $year;
    $unix = strtotime($fulldate);
    $rnd = rand(9,10);
    if($i == 1){
        $sql = "('$unix', '$rev', '$rnd')";
    }else{
        $sql = $sql.','."('$unix', '$rev', '$rnd')";
    }
    $i++;
    
}
echo "INSERT INTO `rev` (`date`, `text`, `rev`) VALUES $sql";

$result = $connect->query("INSERT INTO `rev` (`date`, `text`, `rev`) VALUES $sql");


function mounth($mnth) {
    $months = array("января" => "01", "февраля" => "02", "марта" => "03", "апреля" => "04", "мая" => "05", "июня" => "06", "июля" => "07", "августа" => "08", "сентября" => "09", "октября" => "10", "ноября" => "11", "декабря" => "12");
    $os = strtr($mnth, $months);
    return $os;
}
