<?php

$botToken = "5576928218:AAHnSkpNcoiesf67_w8IlvbMZNXdiZjzciU"; // ����� ����
$website = "https://api.telegram.org/bot" . $botToken;
$result = file_get_contents("$website/getMe");
$result1 = file_get_contents("$website/getWebhookInfo");
echo $result.'<br>'.$result1;
