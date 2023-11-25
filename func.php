<?php
function sendMessage($chat_id, $message, $replyMarkup) {
  file_get_contents($GLOBALS['website'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . $message . '&reply_markup=' . $replyMarkup);
}

function forwardMessage($chat_id, $from_chat_id, $message_id) {
  file_get_contents($GLOBALS['website'] . '/forwardMessage?chat_id=' . $chat_id . '&from_chat_id=' . $from_chat_id . '&message_id=' . $message_id);
}


function answercall ($id){
 file_get_contents($GLOBALS['website'] . '/answerCallbackQuery?callback_query_id=' . $id);
}
function getfile ($fileid){
 $res = file_get_contents($GLOBALS['website'] . '/getFile?file_id=' . $fileid);
 return $res;
}

function popup ($id,$text,$alert){
 file_get_contents($GLOBALS['website'] . '/answerCallbackQuery?callback_query_id=' . $id.'&text='.$text.'&show_alert='.$alert);
}
function editmess ($chat_id, $message_id, $text,$replyMarkup){
file_get_contents($GLOBALS['website'] . '/editMessageText?chat_id=' . $chat_id . '&message_id=' . $message_id . '&text=' . $text.'&reply_markup=' . $replyMarkup);
}
function delmess ($chat_id, $message_id){
file_get_contents($GLOBALS['website'] . '/deleteMessage?chat_id=' . $chat_id . '&message_id=' . $message_id);
}
function sendphoto ($chat_id,$text,$img,$reply){
$url = $GLOBALS['website'].'/sendPhoto';
$params = array(
    'chat_id' => $chat_id, 
    'photo' => $img,
	'caption' => $text,
	'reply_markup' => $reply
);
$result = file_get_contents($url, false, stream_context_create(array(
    'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($params)
    )
)));
}
function sendDocument($chat_id, $text, $doc, $reply) {
    $url = $GLOBALS['website'] . '/sendDocument';
    $params = array(
        'chat_id' => $chat_id,
        'document' => $doc,
        'caption' => $text,
        'reply_markup' => $reply
    );
    $result = file_get_contents($url, false, stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($params)
        )
    )));
}
function sendContact($chat_id,$phone,$name,$reply){
file_get_contents($GLOBALS['website'] . '/sendContact?chat_id=' . $chat_id . '&phone_number=' . $phone . '&first_name=' . $name.'&reply_markup='.$reply);
}

function sendMessagehtml($chat_id, $message, $replyMarkup) {
  file_get_contents($GLOBALS['website'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . $message . '&reply_markup=' . $replyMarkup.'&parse_mode=HTML');
}

function sendgroup($chat_id,$json){
 $url = $GLOBALS['website'] .'/sendMediaGroup';
 $postContent = [
        'chat_id' => $chat_id,
        'media' => $json
		
    ];
$result = file_get_contents($url, false, stream_context_create(array(
    'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
       'content' => http_build_query($postContent)
    ))));
}
function sendlocation($chat_id, $latitude, $longitude,$reply){
file_get_contents($GLOBALS['website'] . '/sendLocation?chat_id=' . $chat_id . '&latitude=' . $latitude . '&longitude=' . $longitude.'&reply_markup='.$reply);

}

function cryptopay($merchid, $it_name, $order_id, $it_des, $check_cur, $inv_am, $inv_cur, $lang, $secret) {
    $sh = sha1("$merchid&$it_name&$order_id&$it_des&$check_cur&$inv_am&$inv_cur&&&$lang&$secret");

    $array = array(
        'merchant_id' => $merchid,
        'item_name' => $it_name,
        'order_id' => $order_id,
        'item_description' => $it_des,
        'checkout_currency' => $check_cur,
        'invoice_amount' => $inv_am,
        'invoice_currency' => $inv_cur,
        'success_url' => '',
        'failed_url' => '',
        'confirmation_policy' => '',
        'language' => $lang,
        'secret_hash' => $sh
    );

    $ch = curl_init('https://api.cryptonator.com/api/merchant/v1/createinvoice');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($html);
    $invid = $res->invoice_id;
    return $invid;
}
function checkinvoice($merchid, $invoice_id, $secret) {
    $sh = sha1("$merchid&$invoice_id&$secret");

    $array = array(
        'merchant_id' => $merchid,
        'invoice_id' => $invoice_id,
        'secret_hash' => $sh
    );



    $ch = curl_init('https://api.cryptonator.com/api/merchant/v1/getinvoice');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}



function sendmessvk($user_id, $random_id, $keyb, $mess, $token){
file_get_contents("https://api.vk.com/method/messages.send?user_id=$user_id&random_id=$random_id&keyboard=$keyb&message=$mess&access_token=$token&v=5.101");
}