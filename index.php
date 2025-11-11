<?php
$telegram_token = '8318568184:AAGPGSF26EnJ--u8O6p2ec9U3NwLi7Y72oY';
$chat_id = '-1003347616327';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

function get_post($key){
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$name = substr(get_post('name'), 0, 200);
$phone = substr(get_post('phone'), 0, 100);
$comment = substr(get_post('message'), 0, 1000);

if ($name === '' || $phone === '') {
    echo 'Пожалуйста, заполните имя и телефон.';
    exit;
}

function esc($s){
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$text = "<b>Новая заявка с лендинга</b>\n" .
        "<b>Имя:</b> " . esc($name) . "\n" .
        "<b>Телефон:</b> " . esc($phone) . "\n" .
        "<b>Комментарий:</b> " . ($comment === '' ? '-' : esc($comment));

$api_url = "https://api.telegram.org/bot{$telegram_token}/sendMessage";
$post_fields = array(
    'chat_id' => $chat_id,
    'text' => $text,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true,
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

if ($result === false) {
    error_log('Telegram send error: ' . $curl_err);
    echo 'Ошибка при отправке. Попробуйте позже.';
    exit;
}

$resp = json_decode($result, true);
if ($http_code === 200 && isset($resp['ok']) && $resp['ok'] === true) {
    header('Location: /?sent=1');
    exit;
} else {
    error_log('Telegram API response: ' . $result);
    echo 'Ошибка API Telegram. Код: ' . htmlspecialchars($http_code);
    exit;
}
?>
