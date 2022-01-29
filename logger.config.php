<?php

use Bitrix\Main\Config\Option;

$MODULE_ID = 'zxkill.logs';

return $config = [
    // основная директория для логов
    'LOG_DIR' => Option::get($MODULE_ID, 'LOG_DIR', '/local/var/logs/'),
    // расширение файлов лога
    'LOG_FILE_EXTENSION' => Option::get($MODULE_ID, 'LOG_FILE_EXTENSION', '.log'),
    // формат времени
    'DATE_FORMAT' => Option::get($MODULE_ID, 'DATE_FORMAT', 'Y-m-d H:i:s'),
    // флаг определяющий нужно ли добавлять разделители в файл лога
    'USE_DELIMITER' => Option::get($MODULE_ID, 'USE_DELIMITER', 'true'),
    // флаг определяющий нужно ли добавлять в запись лога информация о месте вызова метода
    'USE_BACKTRACE' => Option::get($MODULE_ID, 'USE_BACKTRACE', 'true'),
    // флаг определяет нужно ли записывать данные при вызове метода debug()
    'DEV' => Option::get($MODULE_ID, 'DEV', 'true'),
    // символьный код типа почтового события
    'CEVENT_TYPE' => Option::get($MODULE_ID, 'CEVENT_TYPE', 'ZXKILL_LOGS_ALERT'),
    // символьный код шаблона для почтового события
    'CEVENT_MESSAGE' => Option::get($MODULE_ID, 'CEVENT_MESSAGE', 'ZXKILL_LOGS_FATAL_TEMPLATE'),
    //сколько дней хранить логи
    'ROTATE_DAY' => Option::get($MODULE_ID, 'ROTATE_DAY', 5),
    //email для оповещений
    // Если нужно отправлять сообщения сразу на несколько адресов нужно перечислить их через запятую.
    'DEFAULT_EMAIL' => Option::get($MODULE_ID, 'DEFAULT_EMAIL', ''),
    //api ключ полученный в @BotFather
    'TELEGRAM_API_KEY' => Option::get($MODULE_ID, 'TELEGRAM_API_KEY', ''),
    //ID чата, в который отправлять сообщения
    'TELEGRAM_CHAT_ID' => Option::get($MODULE_ID, 'TELEGRAM_CHAT_ID', ''),
    //Куда шлем уведомления
    'HANDLER_EVENT' => Option::get($MODULE_ID, 'HANDLER_EVENT', 'email'),
    //где хранить логи
    'STORAGE' => Option::get($MODULE_ID, 'STORAGE', 'file'),
    //на каких доменах отправлять алерты
    'DOMAIN_FOR_ALERT' => Option::get($MODULE_ID, 'DOMAIN_FOR_ALERT', ''),
];
