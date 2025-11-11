<?php
// telegram.php

// --- Настройте здесь ---
$botToken = '8318568184:AAGPGSF26EnJ--u8O6p2ec9U3NwLi7Y72oY';   // <-- Вставьте токен бота, например 123456:ABC-DEF...
$chatId   = '-1003347616327';     // <-- Вставьте chat_id или id группы (может быть отрицательным для групп)
// -------------------------

// Функция для безопасного получения и очистки поля
function get_post($name) {
    $val = filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);
    if ($val === null) return '';
    return trim($val);
}

// Получаем данные
$first_name = get_post('first_name');
$last_name  = get_post('last_name');
$age        = get_post('age');
$total_sum  = get_post('total_sum');
$monthly    = get_post('monthly_payment');
$marital    = get_post('marital');
$property   = get_post('property');
$phone      = get_post('phone');

// Простая валидация: phone и first_name обязательны
if (empty($first_name) || empty($phone)) {
    http_response_code(400);
    echo 'Missing required fields.';
    exit;
}

// Формируем сообщение (markdownV2 безопасно экранируем спецсимволы)
function tg_escape($text) {
    $search = ['\\','_','*','[',']','(',')','~','`','>','#','+','-','=','|','{','}','.','!'];
    $replace = array_map(function($c){ return '\\'.$c; }, $search);
    return str_replace($search, $replace, $text);
}

$message = "Новая заявка от лендинга:\n";
$message .= "Име: " . $first_name . " " . $last_name . "\n";
$message .= "Възраст: " . $age . "\n";
$message .= "Обща сума: " . $total_sum . " лв.\n";
$message .= "Месечна вноска: " . $monthly . " лв.\n";
$message .= "Семеен статус: " . $marital . "\n";
$message .= "Недвижимо имущество: " . $property . "\n";
$message .= "Телефон: " . $phone . "\n";

// Отправляем через curl
$sendData = [
    'chat_id' => $chatId,
    'text'    => $message,
    'parse_mode' => 'HTML'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/sendMessage");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($result === false || $http_code != 200) {
    // Логируйте ошибку на сервере, если нужно
    error_log('Telegram send error: HTTP code ' . $http_code . ' response: ' . var_export($result, true));
    // Можно перенаправить на страницу ошибки или показать сообщение
    header("HTTP/1.1 500 Internal Server Error");
    echo "Ошибка отправки. Пожалуйста, попробуйте позже.";
    exit;
}

// Успех — перенаправляем на страницу благодарности (создайте её или измените путь)
header("Location: thanks.html");
exit;
