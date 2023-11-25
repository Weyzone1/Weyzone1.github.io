<?php
$botToken = "5621745087:AAHjmmEXmiVSrJeh-s_2TU3P3P55URxbMEU"; // токен бота
$website = "https://api.telegram.org/bot" . $botToken;
$url = "----";
$url1 = urlencode($url);
$result = file_get_contents("$website/setWebhook?url=$url1");
 echo $result;
