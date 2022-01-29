<?php


namespace ZxKill\Logs\Exception;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Mail\Mail;
use ZxKill\Logs\Log;
use ZxKill\Logs\Settings;
use Bitrix\Main\Loader;


class BaseExceptionHandlerLog extends FileExceptionHandlerLog
{
    /**
     * @var string
     */
    protected string $email;

    /**
     * {}
     */
    public function initialize(array $options)
    {
        parent::initialize($options);

        if (!empty($options['email'])) {
            $this->email = $options['email'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($exception, $logType)
    {
        parent::write($exception, $logType);
        $exceptionLog = ExceptionHandlerFormatter::format($exception);
        $exceptionLog = date("Y-m-d H:i:s") . " - Host: " . $_SERVER["HTTP_HOST"] . " - " . static::logTypeToString($logType) . " - " . $exceptionLog . "\n";
        $exceptionLog .= "Request uri: " . $_SERVER['REQUEST_URI'];

        Loader::includeModule('zxkill.logs');

        $settings = Settings::getInstance();

        if (!empty($settings->TELEGRAM_API_KEY()) && !empty($settings->TELEGRAM_CHAT_ID()) || !empty($settings->DEFAULT_EMAIL())) {
            $logger = new Log('exception');
            $logger->fatal('exception', [$exceptionLog]);
        }
        elseif (!empty($this->email)) {
            $mailParams = array(
                'TO' => $this->email,
                'CHARSET' => 'utf8',
                'CONTENT_TYPE' => 'text/plain',
                'SUBJECT' => sprintf('HandlerLog: Host %s', $_SERVER["HTTP_HOST"]),
                'BODY' => $exceptionLog,
                'HEADER' => array(),
            );

            Mail::send($mailParams);
        }
    }
}
