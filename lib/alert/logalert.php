<?php

namespace ZxKill\Logs\Alert;

use ZxKill\Logs\Log;
use ZxKill\Logs\Settings;

abstract class LogAlert
{
    protected $objLog = null;
    protected $message = '';
    protected $settings = null;
    protected $notSend = false;

    public function __construct(Log $obj)
    {
        $this->settings = Settings::getInstance();
        $this->objLog = $obj;
        $this->buildMessage();
        if (!in_array($_SERVER['SERVER_NAME'], explode(PHP_EOL, $this->settings->DOMAIN_FOR_ALERT()))) {
            $this->notSend = true;
        }
    }

    abstract protected static function getName();

    abstract protected function buildMessage();

    abstract protected function getRecipient();

    abstract protected function send();
}
