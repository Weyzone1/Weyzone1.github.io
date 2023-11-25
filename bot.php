<?php
require 'func.php';
require 'db.php';
require 'captcha/generator.php';
$set = parse_ini_file("set.ini", true);
$botToken = $set['tokentg']; // токен бота находится в файле set.ini
$btc = "---";
$ltc = "---";
$rnd = rand(1,2);
switch($rnd){
    case '1':
        $card = "---";
        break;
    case '2':
        $card = "---";
        break;
}
$website = "https://api.telegram.org/bot" . $botToken; // начало ссылки куда отпарвлять методы апи для удобства
$content = file_get_contents("php://input"); //получаем json-ответ
$update = json_decode($content, TRUE); //декодируем json
$message = $update["message"]; //получаем объект Message
$chatId = $message["chat"]["id"]; //ID чата, где должен присутствовать бот
$username = $message["chat"]["first_name"]; //получаем имя пользователя
$text = $message["text"]; //текст сообщения
$callback_query = $update['callback_query'];
$id = $callback_query['id'];
$messid = $callback_query['message']['message_id'];
$mess_id = $message['message_id'];
$data = $callback_query['data'];
$chat_id_in = $callback_query['message']['chat']['id'];
$domen = $_SERVER['SERVER_NAME'];
if (!empty($chatId)) {
    $result = $connect->query("SELECT `lastmess`,`balance` FROM `user_tg` WHERE `chatid` = '$chatId'");
    while ($row = mysqli_fetch_array($result)):;
        $lastmess = $row[0];
        $balance = $row[1];

    endwhile;
}

switch ($text) {
    case 'Отмена':
        $mess = "★ Первый Районный ★ на связи!
Знакомый магазин в новом формате ★ Мы не продаем просрочку!
Только свежие адреса, только проверенные курьеры!
Наш сайт: https://prr24.bz/";
        $img = 'https://' . $domen . '/nnk/images/glav.jpg';
        $inline_button1 = array("text" => "Баланс($balance)", "callback_data" => "/popoln");
        $inline_keyboard = [[$inline_button1]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        $result1 = $connect->query("UPDATE `user_tg` SET `lastmess`= 'null' WHERE `chatid` = '$chatId'");
        sendphoto($chatId, $mess, $img, $reply);
        $mess = "Удачных покупок!";
        require 'key.php';
        sendMessage($chatId, $mess, $reply);
        exit;
}



$finds = "/okr";
$ests = stripos($data, $finds);
if ($ests !== false) {
    $jo = explode("?", $data);
    $idr = $jo[1];
    $result = $connect->query("UPDATE `user_tg` SET `okrug`= '$idr' WHERE chatid = $chat_id_in");
    $result = $connect->query("SELECT `name` FROM `okrug` WHERE id = $idr");
    $row = mysqli_fetch_array($result);
    $okr = $row[0];

    $result = $connect->query("SELECT `id`, `name` FROM `regions` WHERE idokr = $idr");
    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        $idgr = $row[0];
        $name = $row[1];

        if ($i == 0) {
            $inline_button1 = array("text" => "$name", "callback_data" => "/city?$idgr");
            $inline_keyboard = [[$inline_button1]];
            $i++;
        } else {
            $inline_button1 = array("text" => "$name", "callback_data" => "/city?$idgr");
            $inline_keyboard1 = [$inline_button1];
            array_push($inline_keyboard, $inline_keyboard1);
        }
    }
    $keyboard = array("inline_keyboard" => $inline_keyboard);
    $reply = json_encode($keyboard);
    $mess = "Вы выбрали: $okr%0AВыберите город:";
    sendMessage($chat_id_in, $mess, $reply);
    answercall($id);
    exit;
}

$finds1 = "/city";
$ests1 = stripos($data, $finds1);
if ($ests1 !== false) {
    $jo = explode("?", $data);
    $idr = $jo[1];
    $result = $connect->query("UPDATE `user_tg` SET `region`='$idr' , `city` = NULL WHERE chatid = $chat_id_in");
    $result = $connect->query("SELECT `id`, `name` FROM `city` WHERE regid = $idr");
    $i = 0;
    $er = 1;
    while ($row = mysqli_fetch_array($result)) {
        $idregs = $row[0];
        $name = $row[1];
        $er = 0;
        if ($i == 0) {
            $inline_button1 = array("text" => "$name", "callback_data" => "/cits?$idregs");
            $inline_keyboard = [[$inline_button1]];
            $i++;
        } else {
            $inline_button1 = array("text" => "$name", "callback_data" => "/cits?$idregs");
            $inline_keyboard1 = [$inline_button1];
            array_push($inline_keyboard, $inline_keyboard1);
        }
    }
    if ($er == 1) {
        $mess = "Выбор сохранен. Спасибо!";
        require 'key.php';
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        $result = $connect->query("SELECT `region` FROM `user_tg` WHERE chatid = $chat_id_in");
        $row = mysqli_fetch_array($result);
        $region = $row[0];

        $result = $connect->query("SELECT `id`, `name` FROM `category` WHERE JSON_CONTAINS(`dostup`, '$region', '$.reg')");
        $i = 0;
        $es = 1;
        while ($row = mysqli_fetch_array($result)) {
            $idcat = $row[0];
            $name = urlencode($row[1]);
            $es = 0;
            if ($i == 0) {
                $inline_button1 = array("text" => "$name", "callback_data" => "/cat?$idcat");
                $inline_keyboard = [[$inline_button1]];
                $i++;
            } else {
                $inline_button1 = array("text" => "$name", "callback_data" => "/cat?$idcat");
                $inline_keyboard1 = [$inline_button1];
                array_push($inline_keyboard, $inline_keyboard1);
            }
        }
        if ($es == 0) {
            $mess = "Выберите товар:";
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply = json_encode($keyboard);
            sendMessage($chat_id_in, $mess, $reply);
        } else {
            $mess = "Товаров нет.";
            sendMessage($chat_id_in, $mess, $reply);
            exit;
        }
    } else {
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        $mess = "Выберите город:";
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
    }

    exit;
}
$finds2 = "/cits";
$ests2 = stripos($data, $finds2);
if ($ests2 !== false) {
    $jo = explode("?", $data);
    $idr = $jo[1];
    $result = $connect->query("UPDATE `user_tg` SET `city`='$idr' WHERE chatid = $chat_id_in");
    $mess = "Выбор сохранен. Спасибо!";
    require 'key.php';
    sendMessage($chat_id_in, $mess, $reply);
    answercall($id);
    $result = $connect->query("SELECT `region` FROM `user_tg` WHERE chatid = $chat_id_in");
    $row = mysqli_fetch_array($result);
    $region = $row[0];

    $result = $connect->query("SELECT `id`, `name` FROM `category` WHERE JSON_CONTAINS(`dostup`, '$region', '$.reg')");
    $i = 0;
    $es = 0;
    while ($row = mysqli_fetch_array($result)) {
        $idcat = $row[0];
        $name = urlencode($row[1]);
        $es = 1;
        if ($i == 0) {
            $inline_button1 = array("text" => "$name", "callback_data" => "/cat?$idcat");
            $inline_keyboard = [[$inline_button1]];
            $i++;
        } else {
            $inline_button1 = array("text" => "$name", "callback_data" => "/cat?$idcat");
            $inline_keyboard1 = [$inline_button1];
            array_push($inline_keyboard, $inline_keyboard1);
        }
    }
    if ($es == 1) {
        $mess = "Выберите товар:";
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
    } else {
        $mess = "Товаров нет.";
    }
    sendMessage($chat_id_in, $mess, $reply);
    exit;
}

$finds3 = "/cat";
$ests3 = stripos($data, $finds3);
if ($ests3 !== false) {
    $jo = explode("?", $data);
    $idr = $jo[1];
    $result = $connect->query("SELECT `region`, `city` FROM `user_tg` WHERE chatid = $chat_id_in");
    $rw = mysqli_fetch_array($result);
    $regionuser = $rw[0];
    $cityuser = $rw[1];
    if (empty($cityuser)) {
        $result1 = $connect->query("SELECT `name`, `ves`, `id`,`img` FROM `klad` WHERE itid = $idr AND regionid = $regionuser");
    } else {
        $result1 = $connect->query("SELECT `name`, `ves`,`id`,`img` FROM `klad` WHERE itid = $idr AND regionid = $regionuser AND city = $cityuser");
    }

    $i = 0;
    $es = 0;

    while ($row = mysqli_fetch_array($result1)) {
        $name = $row[0];
        $ves = $row[1];
        $idklad = $row[2];
        $img = $row[3];
        $es = 1;

        if ($i == 0) {
            $inline_button1 = array("text" => "$name", "callback_data" => "/klad?$idklad");
            $inline_keyboard = [[$inline_button1]];
            $i++;
        } else {
            $inline_button1 = array("text" => "$name", "callback_data" => "/klad?$idklad");
            $inline_keyboard1 = [$inline_button1];
            array_push($inline_keyboard, $inline_keyboard1);
            $i++;
        }
    }
    if ($es == 1) {
        /*
          if (empty($cityuser)) {
          $result7 = $connect->query("SELECT `name`, `ves` FROM `klad` WHERE itid = $idr AND regionid = $regionuser");
          } else {
          $result7 = $connect->query("SELECT `name`, `ves` FROM `klad` WHERE itid = $idr AND regionid = $regionuser AND city = $cityuser");
          }
          $i = 0;
          $mess = "Наличие по г.%0A";
          while($row = mysqli_fetch_array($result7)){
          $name = $row[0];
          $ves = $row[1];
          $vss = json_decode($ves);
          $cnt = count($vss->ves);
          $mess = $mess.'%0A'.$name.':';
          while($i < $cnt){
          $rr = $vss->ves[$i];
          $rr = round($rr,2);
          $mess = $mess.','.$rr;
          $i++;
          }
          $i = 0;
          $mess = $mess.'г.%0A';
          }

          sendMessage($chat_id_in, $mess, $reply);
         * 
         */
        $mess = "Выберите местоположение:";
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendphoto($chat_id_in, $mess, $img, $reply);
    } else {
        $mess = "Кладов нет.";
        sendMessage($chat_id_in, $mess, $reply);
    }
    answercall($id);
    exit;
}

$finds4 = "/rev";
$ests4 = stripos($data, $finds4);
if ($ests4 !== false) {
    $jo = explode("?", $data);
    $idr = $jo[1];
    $result1 = $connect->query("SELECT `date`, `text`, `rev` FROM `rev` WHERE id = $idr");
    $row = mysqli_fetch_array($result1);
    $date = $row[0];
    $text = $row[1];
    $rev = $row[2];
    $dates = date("d.m.Y", $date);
    $mess = "Отзыв%0A%0AДата: $dates%0AПокупатель: Аноним в боте%0AОценка: $rev из 10%0AОтзыв: $text";
    sendMessage($chat_id_in, $mess, $reply);
    answercall($id);
    exit;
}

$finds5 = "/klad";
$ests5 = stripos($data, $finds5);
if ($ests5 !== false) {
    $mess = "Выберите фасовку";
    $jo = explode("?", $data);
    $idr = $jo[1];
    $result = $connect->query("SELECT `ves`,`itid`,`obz` FROM `klad` WHERE id = $idr");
    $row = mysqli_fetch_array($result);
    $ves = $row[0];
    $itid = $row[1];
    $vss = json_decode($ves);
    $obz = $row[2];
    $count = count($vss->ves);
    $i = 0;
    $result = $connect->query("SELECT `region`, `city` FROM `user_tg` WHERE chatid = $chat_id_in");
    $rw = mysqli_fetch_array($result);
    $regionuser = $rw[0];
    while ($i < $count) {
        $mt = $vss->ves[$i];
        if($regionuser == 21){
            $pr = $vss->pricebel[$i];
        }else{
            $pr = $vss->price[$i];
        }
        
        $mt1 = $mt . '.00';
        if ($i == 0) {
            $inline_button1 = array("text" => "$mt1 $obz - от $pr руб.", "callback_data" => "/itms?$itid?$pr?$mt");
            $inline_keyboard = [[$inline_button1]];
        } else {
            $inline_button1 = array("text" => "$mt1 $obz - от $pr руб.", "callback_data" => "/itms?$itid?$pr?$mt");
            $inline_keyboard1 = [$inline_button1];
            array_push($inline_keyboard, $inline_keyboard1);
        }
        $i++;
    }
    $keyboard = array("inline_keyboard" => $inline_keyboard);
    $reply = json_encode($keyboard);
    sendMessage($chat_id_in, $mess, $reply);
    answercall($id);
    exit;
}

$finds6 = "/itms";
$ests6 = stripos($data, $finds6);
if ($ests6 !== false) {
    $jo = explode("?", $data);
    $itid = $jo[1];
    $pr = $jo[2];
    $mt = $jo[3];
    $mess = "Выбери тип клада";
    $rnd = rand(1, 3);
    $result = $connect->query("UPDATE `user_tg` SET `iditm`= '$itid',`price`='$pr',`ves`='$mt' WHERE chatid = $chat_id_in");
    switch ($rnd) {
        case '1':
            $inline_button1 = array("text" => "Тайник", "callback_data" => "/typekld");
            $inline_keyboard = [[$inline_button1]];
            break;
        case '2':
            $inline_button1 = array("text" => "Прикоп", "callback_data" => "/typekld");
            $inline_keyboard = [[$inline_button1]];
            break;
        case '3':
            $inline_button1 = array("text" => "Прикоп", "callback_data" => "/typekld");
            $inline_button2 = array("text" => "Тайник", "callback_data" => "/typekld");
            $inline_keyboard = [[$inline_button1], [$inline_button2]];
            break;
    }
    $keyboard = array("inline_keyboard" => $inline_keyboard);
    $reply = json_encode($keyboard);
    sendMessage($chat_id_in, $mess, $reply);
    answercall($id);
    exit;
}



$finds7 = "Отзывы";
$ests7 = stripos($text, $finds7);
if ($ests7 !== false) {
        $res = $connect->query("SELECT COUNT(`id`) FROM `rev` WHERE 1");
        $rs = mysqli_fetch_array($res);
        $cn = $rs[0];
        $result = $connect->query("SELECT `id`, `date`, `rev` FROM `rev` WHERE 1 LIMIT 10");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $idt = $row[0];
            $date = $row[1];
            $rev = $row[2];
            $dates = date("d.m.y", $date);
            if ($i == 0) {
                $inline_button1 = array("text" => "$rev/10|$dates", "callback_data" => "/rev?$idt");
                $inline_keyboard = [[$inline_button1]];
                $i++;
            } else {
                $inline_button1 = array("text" => "$rev/10|$dates", "callback_data" => "/rev?$idt");
                $inline_keyboard1 = [$inline_button1];
                array_push($inline_keyboard, $inline_keyboard1);
            }
        }
        $inline_button1 = array("text" => ">>", "callback_data" => "/next");
        $inline_keyboard1 = [$inline_button1];
        array_push($inline_keyboard, $inline_keyboard1);
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        $mess = "Всего отзывов:$cn";
        $result1 = $connect->query("UPDATE `user_tg` SET `smesh`= '10' WHERE `chatid` = '$chatId'");
        sendMessage($chatId, $mess, $reply);
        exit;
}



switch ($data) {
    case '/typekld':
        $mess = "Выберите способ оплаты:";
        $result = $connect->query("SELECT  `price` FROM `user_tg` WHERE  chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $price = $rw[0];
        $result = $connect->query("SELECT `region`, `city` FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $regionuser = $rw[0];
        if($regionuser == 21){
            $pricerub = $price * 24;
            $res = file_get_contents("https://blockchain.info/ticker");
            $enc = json_decode($res);
            $pricebl = $enc->RUB->sell;
            $pricebtc = round($pricerub / $pricebl, 9);
            $binance = file_get_contents("https://api.binance.com/api/v3/depth?symbol=LTCRUB&limit=5");
            $res = json_decode($binance);
            $binance = $res->asks[0][0];
            $binance = round($binance, 0);
            $priceltc = round($pricerub / $binance, 9);
        }else{
            $res = file_get_contents("https://blockchain.info/ticker");
            $enc = json_decode($res);
            $pricebl = $enc->RUB->sell;
            $pricebtc = round($price / $pricebl, 9);
            $binance = file_get_contents("https://api.binance.com/api/v3/depth?symbol=LTCRUB&limit=5");
            $res = json_decode($binance);
            $binance = $res->asks[0][0];
            $binance = round($binance, 0);
            $priceltc = round($price / $binance, 9);
        }
        
        $inline_button1 = array("text" => "Bitcoin(BTC)- $price руб($pricebtc BTC)", "callback_data" => "/btc");
        $inline_button2 = array("text" => "Litecoin(LTC)- $price руб($priceltc LTC)", "callback_data" => "/ltc");
        $inline_button3 = array("text" => "Перевод на карту- $price руб", "callback_data" => "/card");
        $inline_keyboard = [[$inline_button1], [$inline_button2], [$inline_button3]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        break;
    case '/btc':
        $result = $connect->query("UPDATE `user_tg` SET `typepay`= 'btc' WHERE chatid = $chat_id_in");
        $result = $connect->query("SELECT `price`, `iditm`,`okrug`, `region`,`city`,`ves` FROM `user_tg` WHERE  chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $price = $rw[0];
        $itid = $rw[1];
        $idokr = $rw[2];
        $idregi = $rw[3];
        $idcity = $rw[4];
        $mt = $rw[5];
        $result = $connect->query("SELECT  `okrug`, `region`, `city`  FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $idokr = $rw[0];
        $region = $rw[1];
        $city = $rw[2];
        $result = $connect->query("SELECT `name` FROM `okrug` WHERE id = $idokr");
        $rw = mysqli_fetch_array($result);
        $nameokr = $rw[0];
        $result = $connect->query("SELECT `name` FROM `regions` WHERE id = $idregi");
        $rw = mysqli_fetch_array($result);
        $namereg = $rw[0];
        $result = $connect->query("SELECT  `name` FROM `category` WHERE  id = $itid");
        $rw = mysqli_fetch_array($result);
        $nameitms = $rw[0];
        if (!empty($city)) {
            $result = $connect->query("SELECT `name` FROM `city` WHERE  id = $idcity");
            $rw = mysqli_fetch_array($result);
            $namecity = $rw[0];
            $mess = "Информация о заказе%0A%0AЛокация: федеральный округ $nameokr, область $namereg, город $namecity%0AТовар:$nameitms $mt.00 г%0AЦена:$price руб.%0AСпособ оплаты: Bitcoin (BTC)%0A%0AИтого к оплате: точную сумму оплаты вы получите после подтверждения заказа";
        } else {
            $mess = "Информация о заказе%0A%0AЛокация: федеральный округ $nameokr, город $namereg%0AТовар: $nameitms $mt.00 г%0AЦена: $price руб.%0AСпособ оплаты: Bitcoin (BTC)%0A%0AИтого к оплате: точную сумму оплаты вы получите после подтверждения заказа";
        }
        $inline_button1 = array("text" => "Подтвердить", "callback_data" => "/conf");
        $inline_keyboard = [[$inline_button1]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        break;
    case '/ltc':
        $result = $connect->query("UPDATE `user_tg` SET `typepay`= 'ltc' WHERE chatid = $chat_id_in");
        $result = $connect->query("SELECT `price`, `iditm`,`okrug`, `region`,`city`,`ves` FROM `user_tg` WHERE  chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $price = $rw[0];
        $itid = $rw[1];
        $idokr = $rw[2];
        $idregi = $rw[3];
        $idcity = $rw[4];
        $mt = $rw[5];
        $result = $connect->query("SELECT  `okrug`, `region`, `city`  FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $idokr = $rw[0];
        $region = $rw[1];
        $city = $rw[2];
        $result = $connect->query("SELECT `name` FROM `okrug` WHERE id = $idokr");
        $rw = mysqli_fetch_array($result);
        $nameokr = $rw[0];
        $result = $connect->query("SELECT `name` FROM `regions` WHERE id = $idregi");
        $rw = mysqli_fetch_array($result);
        $namereg = $rw[0];
        $result = $connect->query("SELECT  `name` FROM `category` WHERE  id = $itid");
        $rw = mysqli_fetch_array($result);
        $nameitms = $rw[0];
        if (!empty($city)) {
            $result = $connect->query("SELECT `name` FROM `city` WHERE  id = $idcity");
            $rw = mysqli_fetch_array($result);
            $namecity = $rw[0];
            $mess = "Информация о заказе%0A%0AЛокация: федеральный округ $nameokr, область $namereg, город $namecity%0AТовар:$nameitms $mt.00 г%0AЦена:$price руб.%0AСпособ оплаты: Litecoin (LTC)%0A%0AИтого к оплате: точную сумму оплаты вы получите после подтверждения заказа";
        } else {
            $mess = "Информация о заказе%0A%0AЛокация: федеральный округ $nameokr, город $namereg%0AТовар: $nameitms $mt.00 г%0AЦена: $price руб.%0AСпособ оплаты: Litecoin (LTC)%0A%0AИтого к оплате: точную сумму оплаты вы получите после подтверждения заказа";
        }
        $inline_button1 = array("text" => "Подтвердить", "callback_data" => "/conf");
        $inline_keyboard = [[$inline_button1]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        break;
    case '/card':
        $result = $connect->query("UPDATE `user_tg` SET `typepay`= 'card' WHERE chatid = $chat_id_in");
        $result = $connect->query("SELECT `price`, `iditm`,`okrug`, `region`,`city`,`ves` FROM `user_tg` WHERE  chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $price = $rw[0];
        $itid = $rw[1];
        $idokr = $rw[2];
        $idregi = $rw[3];
        $idcity = $rw[4];
        $mt = $rw[5];
        $result = $connect->query("SELECT  `okrug`, `region`, `city`  FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $idokr = $rw[0];
        $region = $rw[1];
        $city = $rw[2];
        $result = $connect->query("SELECT `name` FROM `okrug` WHERE id = $idokr");
        $rw = mysqli_fetch_array($result);
        $nameokr = $rw[0];
        $result = $connect->query("SELECT `name` FROM `regions` WHERE id = $idregi");
        $rw = mysqli_fetch_array($result);
        $namereg = $rw[0];
        $result = $connect->query("SELECT  `name` FROM `category` WHERE  id = $itid");
        $rw = mysqli_fetch_array($result);
        $nameitms = $rw[0];
        if (!empty($city)) {
            $result = $connect->query("SELECT `name` FROM `city` WHERE  id = $idcity");
            $rw = mysqli_fetch_array($result);
            $namecity = $rw[0];
            $mess = "Информация о заказе%0A%0AЛокация: федеральный округ $nameokr, область $namereg, город $namecity%0AТовар:$nameitms $mt.00 г%0AЦена:$price руб.%0AСпособ оплаты: Перевод на карту%0A%0AИтого к оплате: точную сумму оплаты вы получите после подтверждения заказа";
        } else {
            $mess = "Информация о заказе%0A%0AЛокация: федеральный округ $nameokr, город $namereg%0AТовар: $nameitms $mt.00 г%0AЦена: $price руб.%0AСпособ оплаты: Перевод на карту%0A%0AИтого к оплате: точную сумму оплаты вы получите после подтверждения заказа";
        }
        $inline_button1 = array("text" => "Подтвердить", "callback_data" => "/conf");
        $inline_keyboard = [[$inline_button1]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        break;
    case '/conf':
        $nz = rand(0000000, 9999999);
        $kd = rand(00000, 99999);
        $result = $connect->query("SELECT `typepay`,`price` FROM `user_tg` WHERE  chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $typepay = $rw[0];
        $price = $rw[1];
        $prc = $price * 0.12;
        $price = $price + $prc;
        
        $result = $connect->query("SELECT `region`, `city` FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result);
        $regionuser = $rw[0];

        switch ($typepay) {
            case 'btc':
                 if($regionuser == 21){
                     $pricerub = $price * 24;
                    $res = file_get_contents("https://blockchain.info/ticker");
                    $enc = json_decode($res);
                    $pricebl = $enc->RUB->sell;
                    $pricebtc = round($pricerub / $pricebl, 9);
                 }else{
                     $res = file_get_contents("https://blockchain.info/ticker");
                    $enc = json_decode($res);
                    $pricebl = $enc->RUB->sell;
                    $pricebtc = round($price / $pricebl, 9);
                 }
                $mess = "Номер заказа: OAEF-$nz%0AКод доступа: $kd%0A%0AОплатите заказ в течении 60 мин.%0A%0AЕсли у вас возникли какие-либо проблемы с оплатой, обратитесь в поддержку  и перешлите туда это сообщение или приложите этот номер заказа: OAEF-$nz.%0A%0AВнимание! Заявка будет оплачена после 1-го подтверждения сети Bitcoin (BTC)!%0A%0AВнимание! Этот кошелек одноразовый и актуален только в этой заявке!%0A%0AМожно округлить и перевести больше, но меньше переводить нельзя!%0A%0AПереведите (BTC) не менее:";
                $keyboard = array(array("Отмена"));
                $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => false);
                $reply = json_encode($resp);
                sendMessage($chat_id_in, $mess, $reply);
                sendMessage($chat_id_in, $pricebtc, $reply);
                $mess = "На Bitcoin (BTC) адрес:";
                sendMessage($chat_id_in, $mess, $reply);
                sendMessage($chat_id_in, $btc, $reply);
                $mess = "Если вы передумали, и не хотите платить нажмите кнопку ниже.";
                $inline_button1 = array("text" => "Отмена", "callback_data" => "/cancel");
                $inline_keyboard = [[$inline_button1]];
                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $reply = json_encode($keyboard);
                sendMessage($chat_id_in, $mess, $reply);
                break;
            case 'ltc':
                if($regionuser == 21){
                     $pricerub = $price * 24;
                    $binance = file_get_contents("https://api.binance.com/api/v3/depth?symbol=LTCRUB&limit=5");
                    $res = json_decode($binance);
                    $binance = $res->asks[0][0];
                    $binance = round($binance, 0);
                    $priceltc = round($pricerub / $binance, 9);
                 }else{
                    $binance = file_get_contents("https://api.binance.com/api/v3/depth?symbol=LTCRUB&limit=5");
                    $res = json_decode($binance);
                    $binance = $res->asks[0][0];
                    $binance = round($binance, 0);
                    $priceltc = round($price / $binance, 9);
                 }
                
                $mess = "Номер заказа: OAEF-$nz%0AКод доступа: $kd%0A%0AОплатите заказ в течении 60 мин.%0A%0AЕсли у вас возникли какие-либо проблемы с оплатой, обратитесь в поддержку  и перешлите туда это сообщение или приложите этот номер заказа: OAEF-$nz.%0A%0AВнимание! Заявка будет оплачена после 1-го подтверждения сети Litecoin (LTC)!%0A%0AВнимание! Этот кошелек одноразовый и актуален только в этой заявке!%0A%0AМожно округлить и перевести больше, но меньше переводить нельзя!%0A%0AПереведите (LTC) не менее:";
                $keyboard = array(array("Отмена"));
                $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => false);
                $reply = json_encode($resp);
                sendMessage($chat_id_in, $mess, $reply);
                sendMessage($chat_id_in, $priceltc, $reply);
                $mess = "На Litecoin (LTC) адрес:";
                sendMessage($chat_id_in, $mess, $reply);
                sendMessage($chat_id_in, $ltc, $reply);
                $mess = "Если вы передумали, и не хотите платить нажмите кнопку ниже.";
                $inline_button1 = array("text" => "Отмена", "callback_data" => "/cancel");
                $inline_keyboard = [[$inline_button1]];
                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $reply = json_encode($keyboard);
                sendMessage($chat_id_in, $mess, $reply);
                break;
            case 'card':
                $mess = "Номер заказа: OHPH-$nz%0AКод доступа: $kd%0A%0AВНИМАНИЕ! Переводите РОВНО указанную сумму! Ни больше, ни меньше!%0AПереведите (ровно эту сумму): $price руб%0A%0AНа номер карты: $card%0A%0AПеревод нужно сделать в течении 50 мин.%0A%0AПрочитайте перед оплатой:%0A%0A1. Вы должны перевести ровно указанную сумму (не больше и не меньше), иначе ваш платеж зачислен не будет!. При переводе не точной суммы вы можете оплатить чужой заказ и потерять средства.%0A%0A2. Делайте перевод одним платежом, если вы разобьете платеж на несколько, ваш платеж зачислен не будет!%0A%0A3. Перевод нужно осуществить в течении 50 мин. после создания заказа. Если вам не хватает времени, отмените заявку и создайте новую!%0A%0A4. Если у вас возникли какие-либо проблемы с оплатой, обратитесь в поддержку  и перешлите туда это сообщение или приложите этот номер заказа: OHPH-$nz и $kd. Проблемы с оплатой рассматриваются в течении 48 часов.%0A%0AПосле оплаты, подождите 5-10 минут, наша система проверит ваш платеж и выдаст товар. НЕ ОТМЕНЯЙТЕ ЗАКАЗ без крайней необходимости.";
                $keyboard = array(array("Отмена"));
                $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => false);
                $reply = json_encode($resp);
                sendMessage($chat_id_in, $mess, $reply);
                $mess = "Если вы передумали, и не хотите платить нажмите кнопку ниже.";
                $inline_button1 = array("text" => "Отмена", "callback_data" => "/cancel");
                $inline_keyboard = [[$inline_button1]];
                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $reply = json_encode($keyboard);
                sendMessage($chat_id_in, $mess, $reply);
                break;
        }
        answercall($id);
        break;
    case '/cancel':
        $mess = "★ Первый Районный ★ на связи!
Знакомый магазин в новом формате ★ Мы не продаем просрочку!
Только свежие адреса, только проверенные курьеры!
Наш сайт: https://prr24.bz/";
        $img = 'https://' . $domen . '/nnk/images/glav.jpg';
        sendphoto($chat_id_in, $mess, $img, $reply);
        $mess = "Удачных покупок";
        require 'key.php';
        sendMessage($chat_id_in, $mess, $reply);
        break;
    case '/history':
        $mess = "У вас нет ни одного заказа!";
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        exit;
    case '/jobs':
        $mess = "Ваш код реферала: $chatId%0AСсылка: http://t.me/hyd72t63a_bot?start=$chatId%0AРаспространяйте ваш реферальный код или ссылку. Когда покупатель зарегистрируется с вашим кодом, он получит награду. Если он сделает покупку, вы получите вознаграждение.";
        sendMessage($chat_id_in, $mess, $reply);
        $mess = "Вы можете заработать приглашая покупателей.%0AС каждой покупки приглашенного вами покупателя вам полагается награда в размере 3.0% от суммы его покупки%0AПриглашенный вами покупатель так же получит награду в размере 300 руб на баланс его аккаунта.";
        $inline_button1 = array("text" => "Подробнее", "callback_data" => "/inf");
        $inline_keyboard = [[$inline_button1]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        exit;
    case '/inf':
        $mess = "Кол-во ваших рефералов: 0%0AПокупки рефералов: 0%0AСумма покупок рефералов: 0 руб%0AВаш заработок: 0 руб";
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        exit;
    case '/promo':
        $mess = "Активация купона%0A%0AВведите код купона:";
        $result1 = $connect->query("UPDATE `user_tg` SET `lastmess`= 'promo' WHERE `chatid` = '$chat_id_in'");
        $keyboard = array(array("Отмена"));
        $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => false);
        $reply = json_encode($resp);
        sendMessage($chat_id_in, $mess, $reply);
        answercall($id);
        exit;
    case '/next':
        $result1 = $connect->query("SELECT `smesh` FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result1);
        $smesh = $rw[0];

        $result = $connect->query("SELECT `id`, `date`, `rev` FROM `rev` WHERE 1 ORDER by id LIMIT $smesh,10");
        $i = 0;
        $es = 0;
        while ($row = mysqli_fetch_array($result)) {
            $idt = $row[0];
            $date = $row[1];
            $rev = $row[2];
            $dates = date("d.m.y", $date);
            $es = 1;
            if ($i == 0) {
                $inline_button1 = array("text" => "$rev/10|$dates", "callback_data" => "/rev?$idt");
                $inline_keyboard = [[$inline_button1]];
                $i++;
            } else {
                $inline_button1 = array("text" => "$rev/10|$dates", "callback_data" => "/rev?$idt");
                $inline_keyboard1 = [$inline_button1];
                array_push($inline_keyboard, $inline_keyboard1);
            }
        }
        if ($es == 1) {
            $inline_button1 = array("text" => ">>", "callback_data" => "/next");
            $inline_button2 = array("text" => "<<", "callback_data" => "/back");
            $inline_keyboard1 = [$inline_button2, $inline_button1];
            array_push($inline_keyboard, $inline_keyboard1);
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply = json_encode($keyboard);
            $mess = "Всего отзывов:1";
            $result1 = $connect->query("UPDATE `user_tg` SET `smesh`= `smesh` + 10 WHERE `chatid` = '$chat_id_in'");
            editmess($chat_id_in, $messid, $mess, $reply);
            answercall($id);
        } else {
            $mess = "Результатов больше нет";
            popup($id, $mess, '0');
        }
        exit;
    case '/back':
        $result1 = $connect->query("SELECT `smesh` FROM `user_tg` WHERE chatid = $chat_id_in");
        $rw = mysqli_fetch_array($result1);
        $smesh = $rw[0];
        $smesh = $smesh - 20;
        $result = $connect->query("SELECT `id`, `date`, `rev` FROM `rev` WHERE 1 ORDER by id LIMIT $smesh,10");
        $i = 0;
        $es = 0;
        while ($row = mysqli_fetch_array($result)) {
            $idt = $row[0];
            $date = $row[1];
            $rev = $row[2];
            $dates = date("d.m.y", $date);
            $es = 1;
            if ($i == 0) {
                $inline_button1 = array("text" => "$rev/10|$dates", "callback_data" => "/rev?$idt");
                $inline_keyboard = [[$inline_button1]];
                $i++;
            } else {
                $inline_button1 = array("text" => "$rev/10|$dates", "callback_data" => "/rev?$idt");
                $inline_keyboard1 = [$inline_button1];
                array_push($inline_keyboard, $inline_keyboard1);
            }
        }
        if ($es == 1) {
            $inline_button1 = array("text" => ">>", "callback_data" => "/next");
            $inline_button2 = array("text" => "<<", "callback_data" => "/back");
            if ($smesh == 0) {
                $inline_keyboard1 = [$inline_button1];
            } else {
                $inline_keyboard1 = [$inline_button2, $inline_button1];
            }

            array_push($inline_keyboard, $inline_keyboard1);
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply = json_encode($keyboard);
            $mess = "Всего отзывов:1";
            $result1 = $connect->query("UPDATE `user_tg` SET `smesh`= `smesh` - 10 WHERE `chatid` = '$chat_id_in'");
            editmess($chat_id_in, $messid, $mess, $reply);
            answercall($id);
        } else {
            $mess = "Результатов больше нет";
            popup($id, $mess, '0');
        }
        exit;
}


switch ($lastmess) {
    case 'captcha':
        $result1 = $connect->query("SELECT `answercapt` FROM `user_tg` WHERE `chatid` = '$chatId'");
        $answer = mysqli_fetch_array($result1);
        $answer = $answer[0];
        if ($answer == $text) {
            $mess = 'Вы не робот:) можете пользоваться ботом!';
            sendMessage($chatId, $mess, $reply);
            $result1 = $connect->query("UPDATE `user_tg` SET `lastmess`= 'null' WHERE `chatid` = '$chatId'");
            unlink('captcha/' . $chatId . '_' . $answer . '.png');
            $mess = "★ Первый Районный ★ на связи!
Знакомый магазин в новом формате ★ Мы не продаем просрочку!
Только свежие адреса, только проверенные курьеры!
Наш сайт: https://prr24.bz/";
            $img = 'https://' . $domen . '/nnk/images/glav.jpg';
            $inline_button1 = array("text" => "Баланс($balance)", "callback_data" => "/popoln");
            $inline_keyboard = [[$inline_button1]];
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply = json_encode($keyboard);
            sendphoto($chatId, $mess, $img, $reply);
            $result2 = $connect->query("SELECT `id`, `name` FROM `okrug` WHERE 1");
            $i = 0;
            while ($row = mysqli_fetch_array($result2)) {
                $idokr = $row[0];
                $name = $row[1];
                if ($i == 0) {
                    $inline_button1 = array("text" => "$name", "callback_data" => "/okr?$idokr");
                    $inline_keyboard = [[$inline_button1]];
                    $i++;
                } else {
                    $inline_button1 = array("text" => "$name", "callback_data" => "/okr?$idokr");
                    $inline_keyboard1 = [$inline_button1];
                    array_push($inline_keyboard, $inline_keyboard1);
                }
            }
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply = json_encode($keyboard);
            $mess = "Выберите федеральный округ:";
            sleep(1);
            sendMessage($chatId, $mess, $reply);
        } else {
            $captcha = captcha($chatId);
            $img = 'https://' . $domen . '/nnk/captcha/' . $chatId . '_' . $captcha . '.png';
            $mess = "Вы не правильно решили капчу, попробуйте еще раз.";
            sendphoto($chatId, $mess, $img, $reply);
            $result1 = $connect->query("UPDATE `user_tg` SET `answercapt`= '$captcha' WHERE `chatid` = '$chatId'");
            unlink('captcha/' . $chatId . '_' . $answer . '.png');
        }
        break;
    case 'promo':
        $mess = "Вы ввели не корректный код купона! Попробуйте еще раз.%0A%0AВведите код купона:";
        sendMessage($chatId, $mess, $reply);
        exit;
}

$result = $connect->query("SELECT COUNT(`id`) FROM `rev` WHERE 1");
$rw = mysqli_fetch_array($result);
$cn = $rw[0];
$otzv = 'Отзывы ('.$cn.')';

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
$items = 'Товары('.$namecity.')';
switch ($text) {
    case '/start':
        $result = $connect->query("SELECT `id` FROM `user_tg` WHERE `chatid` = '$chatId'");
        while ($row = mysqli_fetch_array($result)):;
            $reg = $row[0];
        endwhile;
        if (empty($reg)) {
            $captcha = captcha($chatId);
            $pathc = $_SERVER['SCRIPT_NAME'];
            $dirname = dirname($pathc);
 
            $img = 'https://' . $domen . '/'.$dirname.'/captcha/' . $chatId . '_' . $captcha . '.png';
            $mess = "Нам необходимо убедиться что вы не робот! Пожалуйста, введите и отправьте цифры с картинки";
            sendphoto($chatId, $mess, $img, $reply);
            $result = $connect->query("INSERT INTO `user_tg`(`chatid`, `balance`, `lastmess`, `answercapt`) VALUES ('$chatId','0','captcha','$captcha')");
        }else{
            $mess = "★ Первый Районный ★ на связи!
Знакомый магазин в новом формате ★ Мы не продаем просрочку!
Только свежие адреса, только проверенные курьеры!
Наш сайт: https://prr24.bz/";
        $img = 'https://' . $domen . '/nnk/images/glav.jpg';
        sendphoto($chatId, $mess, $img, $reply);
        $mess = "Удачных покупок";
        require 'key.php';
        sendMessage($chatId, $mess, $reply);
       
        }
        break;
    case 'Локации':
        $result2 = $connect->query("SELECT `id`, `name` FROM `okrug` WHERE 1");
        $i = 0;
        while ($row = mysqli_fetch_array($result2)) {
            $idokr = $row[0];
            $name = $row[1];
            if ($i == 0) {
                $inline_button1 = array("text" => "$name", "callback_data" => "/okr?$idokr");
                $inline_keyboard = [[$inline_button1]];
                $i++;
            } else {
                $inline_button1 = array("text" => "$name", "callback_data" => "/okr?$idokr");
                $inline_keyboard1 = [$inline_button1];
                array_push($inline_keyboard, $inline_keyboard1);
            }
        }
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        $mess = "Выберите федеральный округ:";
        sendMessage($chatId, $mess, $reply);
        break;
    case $items:
        $result = $connect->query("SELECT `region` FROM `user_tg` WHERE chatid = $chatId");
        $row = mysqli_fetch_array($result);
        $region = $row[0];
        $result = $connect->query("SELECT `id`, `name` FROM `category` WHERE JSON_CONTAINS(`dostup`, '$region', '$.reg')");
        $i = 0;
        $es = 0;
        while ($row = mysqli_fetch_array($result)) {
            $idcat = $row[0];
            $name = urlencode($row[1]);
            $es = 1;
            if ($i == 0) {
                $inline_button1 = array("text" => "$name", "callback_data" => "/cat?$idcat");
                $inline_keyboard = [[$inline_button1]];
                $i++;
            } else {
                $inline_button1 = array("text" => "$name", "callback_data" => "/cat?$idcat");
                $inline_keyboard1 = [$inline_button1];
                array_push($inline_keyboard, $inline_keyboard1);
            }
        }
        if ($es == 1) {
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply = json_encode($keyboard);
            $mess = "Выберите товар:";
        } else {
            $mess = "Товаров нет.";
        }
        sendMessage($chatId, $mess, $reply);
        break;
    case 'Профиль':
        $mess = "Профиль%0A%0AВаш ID: $chatId%0AВаш баланс: $balance руб.%0AЗаказов: 0%0AНа сумму: 0 руб.%0AСредний чек: 0 руб.%0AВаша скидка: 0%%0A%0AВаша группа покупателя: Начальная";
        $inline_button1 = array("text" => "История заказов", "callback_data" => "/history");
        //$inline_button2 = array("text" => "Пополнить баланс", "callback_data" => "/pay");
        $inline_button3 = array("text" => "Заработать", "callback_data" => "/jobs");
        $inline_button4 = array("text" => "Активировать купон", "callback_data" => "/promo");
        $inline_keyboard = [[$inline_button1], [$inline_button3], [$inline_button4]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chatId, $mess, $reply);
        break;
    case 'Поддержка':
        $mess = "Поддержка%0A%0AЕсли у вас возникла проблема с оплатой, то пишите в наш бот поддержки:%0A@PervyyRayon%0A(напишите /start)%0A%0A%0AПо всем остальным вопросам%0A%0AЕсли у вас есть вопросы по заказу, можете написать нам: @PervyyRayon";
        sendMessage($chatId, $mess, $reply);
        break;
    
    case 'Заработать':
        $mess = "Ваш код реферала: $chatId%0AСсылка: http://t.me/hyd72t63aa_bot?start=$chatId%0AРаспространяйте ваш реферальный код или ссылку. Когда покупатель зарегистрируется с вашим кодом, он получит награду. Если он сделает покупку, вы получите вознаграждение.";
        sendMessage($chatId, $mess, $reply);
        $mess = "Вы можете заработать приглашая покупателей.%0AС каждой покупки приглашенного вами покупателя вам полагается награда в размере 3.0% от суммы его покупки%0AПриглашенный вами покупатель так же получит награду в размере 300 руб на баланс его аккаунта.";
        $inline_button1 = array("text" => "Подробнее", "callback_data" => "/inf");
        $inline_keyboard = [[$inline_button1]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply = json_encode($keyboard);
        sendMessage($chatId, $mess, $reply);
        exit;
    case 'Отмена':
        $mess = "★ Первый Районный ★ на связи!
Знакомый магазин в новом формате ★ Мы не продаем просрочку!
Только свежие адреса, только проверенные курьеры!
Наш сайт: https://prr24.bz/";
        $img = 'https://' . $domen . '/nnk/images/glav.jpg';
        sendphoto($chat_id_in, $mess, $img, $reply);
        $mess = "Удачных покупок";
        require 'key.php';
        sendMessage($chat_id_in, $mess, $reply);
        break;
}

?>

