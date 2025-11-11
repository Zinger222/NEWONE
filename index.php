<?php
// --- Настройки (замени токен, он скомпрометирован) ---
$botToken = 'REPLACE_WITH_NEW_TOKEN';
$chatId   = '-1003347616327';

// Покажем, что эндпоинт жив, если открыть в браузере
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(200);
    echo "Endpoint is alive. Use POST.";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

// Читаем поля
function post($k){ $v = $_POST[$k] ?? ''; return is_string($v) ? trim($v) : ''; }
function h($s){ return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

$first_name = post('first_name');
$last_name  = post('last_name');
$age        = post('age');
$total_sum  = post('total_sum');
$monthly    = post('monthly_payment');
$marital    = post('marital');
$property   = post('property');
$phone      = post('phone');

if ($first_name === '' || $phone === '') {
    http_response_code(400);
    echo "Missing required fields: first_name, phone.";
    exit;
}

// Сообщение
$msg  = "<b>Новая заявка от лендинга</b>\n";
$msg .= "Име: " . h($first_name) . " " . h($last_name) . "\n";
$msg .= "Възраст: " . h($age) . "\n";
$msg .= "Обща сума: " . h($total_sum) . " лв.\n";
$msg .= "Месечна вноска: " . h($monthly) . " лв.\n";
$msg .= "Семеен статус: " . h($marital) . "\n";
$msg .= "Недвижимо имущество: " . h($property) . "\n";
$msg .= "Телефон: " . h($phone) . "\n";

// Отправка
$payload = [
    'chat_id'    => $chatId,
    'text'       => $msg,
    'parse_mode' => 'HTML',
];

$ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($res !== false && $code === 200) {
    header('Location: /thanks.html');  // при желании создайте страницу
    exit;
}

error_log("Telegram send error: HTTP {$code}; CURL: {$err}; Response: {$res}");
http_response_code(500);
echo "Ошибка отправки. Попробуйте позже.";
