<?php

namespace ZxKill\Logs\Alert;

use ZxKill\Logs\Log;

final class AlertEmail extends LogAlert
{
    protected $subject = '';

    public function __construct(Log $obj)
    {
        parent::__construct($obj);
        $this->buildSubject();
    }

    protected function buildMessage()
    {
        $arMessage = [];
        $execFilePath = $this->objLog->getExecPathFile();

        if ($execFilePath) {
            $arMessage[] = 'Файл вызова: <b>' . $execFilePath . '</b>';
        }

        $arMessage[] = 'Код лога: <b>' . $this->objLog->getLoggerCode() . '</b>';
        $arMessage[] = 'Данные: <br/><i>' . implode('<br>', $this->objLog->getLogDataItems()) . '</i>';

        $this->message = implode('<br>', $arMessage);
    }

    protected function buildSubject()
    {
        //todo: тут нужно вынести тему для сообщения в настройки
        $this->subject = 'ZxKill.Logs:' . $_SERVER['SERVER_NAME'] . ':' . $this->objLog->getLoggerCode();
    }

    protected function getRecipient(): string
    {
        return $this->settings->DEFAULT_EMAIL();
    }

    public function send(): bool
    {
        if ($this->notSend) {
            return false;
        }
        if (!empty($this->settings->DEFAULT_EMAIL())) {
            $arEventFields = [
                'EMAIL_TO' => $this->getRecipient(),
                'SUBJECT' => $this->subject,
                'MESSAGE' => $this->message,
            ];

            \CEvent::SendImmediate(
                $this->settings->CEVENT_TYPE(),
                SITE_ID,
                $arEventFields,
                'N',
                $this->settings->CEVENT_MESSAGE()
            );
        }
        return true;
    }

    public static function getName(): string
    {
        return 'Email';
    }
}
