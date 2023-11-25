<?php
require 'db.php';

$result = $connect->query("SELECT `id` `name` FROM `regions` WHERE 1");

while($row = mysqli_fetch_array($result)){
    $idreg = $row[0];
    $namereg = $row[1];
    $result1 = $connect->query("SELECT `id`, `name`, `regid` FROM `city` WHERE regid = $idreg");
    $rw = mysqli_fetch_array($result1);
    $idcit = $rw[0];
    if(!empty($idcit)){
        $result1 = $connect->query("SELECT `id`, `name`, `regid` FROM `city` WHERE regid = $idreg");
        while($rw = mysqli_fetch_array($result1)){
            $idcity = $rw[0];
            $namecity = $rw[1];
            $ves = '{"ves":[1.00,3.00,5.00],"price":[2800,6400,13900]';
            $result2 = $connect->query("INSERT INTO `klad`(`name`, `ves`, `regionid`, `city`, `itid`, `img`) VALUES ('$namecity','$ves','$idreg','$idcity','34','https://hungersshow.ru/nnk/images/mef.jpg')");
            
        }
    } else {
        $result2 = $connect->query("INSERT INTO `klad`(`name`, `ves`, `regionid`, `itid`, `img`) VALUES ('$namereg','$ves','$idreg','34','https://hungersshow.ru/nnk/images/mef.jpg')");
    }
    
}
