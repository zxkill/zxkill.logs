<?php

namespace ZxKill\Logs\Alert;

use Telegram\Bot\Api;
use ZxKill\Logs\Log;

final class AlertTelegram extends LogAlert
{

    public function __construct(Log $obj)
    {
        parent::__construct($obj);
    }

    protected function buildMessage(): bool
    {
        $arMessage = [];
        $execFilePath = $this->objLog->getExecPathFile();

        if ($execFilePath) {
            $arMessage[] = 'Файл вызова: <b>' . $execFilePath . '</b>';
        }

        $arMessage[] = "Код лога: <b>" . $this->objLog->getLoggerCode() . "</b>";
        $arMessage[] = "Данные: \n<i>" . implode("\n", $this->objLog->getLogDataItems()) . "</i>";

        $this->message = implode("\n", $arMessage);
        return true;
    }

    protected function getRecipient(): string
    {
        return $this->settings->TELEGRAM_CHAT_ID();
    }

    public static function getName(): string
    {
        return 'Telegram';
    }

    /**
     * Посылает сообщение в телеграм канал
     * Id чата и токен бота задаются в доп настройках
     * @return mixed
     */
    public function send()
    {
        if ($this->notSend) {
            return false;
        }
        try {
            $chatId = $this->settings->TELEGRAM_CHAT_ID();
            $botToken = $this->settings->TELEGRAM_API_KEY();

            if ($chatId && $botToken) {
                $url = sprintf(
                    "https://149.154.167.220/bot%s/%s",
                    $botToken,
                    "sendMessage"
                );

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_HEADER => false,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_HTTPHEADER => array('Accept-Language: ru,en-us', 'Host: api.telegram.org'),
                    CURLOPT_POSTFIELDS => [
                        'chat_id' => $chatId,
                        'text' => $this->message,
                        'parse_mode' => 'HTML'
                    ]
                ]);

                $response = curl_exec($ch);

                return json_decode($response);
            } else {
                return false;
            }
        } catch (\Exception $ex) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/local/var/logs/telegram_messages.txt",
                date("Y-m-d H:i:s") . " Не удалось отправить сообщение в Telegram канал: " . $ex->getMessage(),
                FILE_APPEND | LOCK_EX);
        }
        return true;
    }
}
