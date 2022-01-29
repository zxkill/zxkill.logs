<?php

namespace ZxKill\Logs\Storage;

use ZxKill\Logs\Log;
use ZxKill\Logs\Settings;

abstract class LogStorage
{
    protected ?Log $objLog = null;
    protected ?Settings $settings = null;

    abstract protected static function getName();
    abstract protected function rotateLog();
    abstract protected function writeInStorage();

    public function __construct(Log $obj)
    {
        $this->settings = Settings::getInstance();
        $this->objLog = $obj;
    }
}
