<?php

require 'db.php';

$file = file_get_contents("cits.txt");
$arr = explode('$', $file);

foreach ($arr as $value) {
    $val = explode(';', $value);
    $count = count($val);
    $i = 1;
    $namecity = trim($val[0]);

    $result = $connect->query("SELECT `id`, `regid` FROM `city` WHERE `name` LIKE '$namecity'");
    $rw = mysqli_fetch_array($result);
    $idcit = $rw[0];
    $regid = $rw[1];
    if (!empty($idcit)) {
        while ($i < $count) {
            $vars = $val[$i];
            $i++;
            $ves = '{"ves":[1.00,5.00],"price":[3400,12900]}';
            $connect->query("INSERT INTO `klad`(`name`, `ves`, `regionid`, `city`, `itid`, `img`) VALUES ('$vars','$ves','$regid','$idcit','31','https://hungersshow.ru/nnk/images/alpha.jpg')");
            
        }
    }
}

