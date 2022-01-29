<?php

namespace ZxKill\Logs;

/**
 * Class Log
 * @method void debug(string $message, array|object $context)
 * @method void log(string $message, array|object $context)
 * @method void info(string $message, array|object $context)
 * @method void warning(string $message, array|object $context)
 * @method void error(string $message, array|object $context)
 * @method void fatal(string $message, array|object $context)
 */
class Log
{
    /**
     * @var string
     */
    protected string $logTemplate = "{date} {level} {file} {message} {context}";

    /**
     * @var array
     */
    protected array $logLevel = [
        1 => 'debug',
        2 => 'info',
        3 => 'warning',
        4 => 'error',
        5 => 'fatal',
        6 => 'timer',
    ];

    /**
     * @var string
     */
    protected string $dateFormat = '';

    /**
     * @var bool|mixed|string
     */
    protected $loggerCode = false;

    /**
     * @var settings|null
     */
    protected ?settings $settings = null;

    /**
     * @var array
     */
    protected array $logData = [
        'ITEMS' => []
    ];

    /**
     * @var bool|int
     */
    protected $identifier = false;
    /**
     * @var bool
     */
    protected bool $useDelimiter = false;

    /**
     * @var bool
     */
    protected bool $sendAlert = false;
    /**
     * @var bool
     */
    protected bool $useBacktrace = false;
    protected $storage = null;

    protected bool $execPathFile = false;

    /**
     * @var array
     */
    protected array $timers = [];

    /**
     * @throws \Exception
     */
    public function __construct($code = false)
    {
        $this->settings = Settings::getInstance();
        $storageMethod = 'ZxKill\Logs\Storage\\' . $this->settings->STORAGE();
        if (class_exists($storageMethod)) {
            $this->storage = new $storageMethod($this);
            $this->dateFormat = $this->settings->DATE_FORMAT();
            $this->loggerCode = (!empty($code)) ? str_replace(['/', '\\'], '_', $code) : 'common';
            $this->identifier = getmypid();

            if ($this->settings->USE_DELIMITER() == true) {
                $this->useDelimiter = true;
            }

            if ($this->settings->USE_BACKTRACE() == true) {
                $this->useBacktrace = true;
            }
        } else {
            throw new \Exception('Метод хранения логов не обнаружен');
        }
    }

    /**
     * @return bool|mixed|string
     */
    public function getLoggerCode()
    {
        return $this->loggerCode;
    }

    /**
     * Возвращает текстовый код уровня лога
     * @param int $code
     * @return mixed
     */
    protected function getLogLevel(int $code = 1): string
    {
        return (array_key_exists($code, $this->logLevel)) ? $this->logLevel[$code] : $this->logLevel[1];
    }

    /**
     * Метод формирует сообщение лога согласно шаблону и добавляет сообщение в свойство $this->logData
     * @param int $level уровень лога
     * @param bool $msg сообщение
     * @param bool $context доп. информация
     */
    protected function write($level, $msg = false, $context = false)
    {
        $date = new \DateTime();
        $date = '[' . $date->format($this->dateFormat) . ']';
        $level = '[:' . $this->getLogLevel($level) . ']';

        if ($this->useDelimiter === false) {
            $level .= ' [ pid:' . $this->identifier . ']';
        }

        $file = '';
        $message = (!empty($msg)) ? $msg : '';
        $logContext = '';

        if ($this->useBacktrace) {
            $execLogMethodFileData = $this->backtrace();
            $this->execPathFile = $execLogMethodFileData['file'];

            if (!empty($execLogMethodFileData)) {
                $strBacktraceData = implode(':', $execLogMethodFileData);

                // @crunch: маленький костыль для таймера выполнения. чтобы лишний раз не вызывать backtrace();
                if (is_array($context) && array_key_exists('STOP_POINT', $context) && empty($context['STOP_POINT'])) {
                    $context['STOP_POINT'] = $strBacktraceData;
                }

                $file = '[' . $strBacktraceData . ']';
            }
        }

        if (!empty($context)) {
            $logContext = (is_array($context) || is_object($context)) ? print_r($context, 1) : $context;
        }

        $logData = [
            $date,
            $level,
            $file,
            $message,
            $logContext
        ];

        $logString = str_replace(['{date}', '{level}', '{file}', '{message}', '{context}'], $logData, $this->logTemplate) . PHP_EOL;

        $this->setLogItem($logString);
    }

    /**
     * @return mixed
     */
    public function getLogDataItems()
    {
        return $this->logData['ITEMS'];
    }

    /**
     * Указываем флаг о том, что нужно отправлять оповещения на почту
     */
    public function sendAlert()
    {
        $this->sendAlert = true;
    }

    public function needSendAlert(): bool
    {
        return $this->sendAlert;
    }

    /**
     * @param $item
     */
    protected function setLogItem($item)
    {
        $this->logData['ITEMS'][] = $item;
    }

    /**
     * Получаем данные о вызове методов логирования в проекте
     * @return array
     */
    protected function backtrace(): array
    {
        $return = [];
        $backtraceData = debug_backtrace(false, 3);
        $execMethodRecord = array_pop($backtraceData);

        if (!empty($execMethodRecord) && is_array($execMethodRecord)) {
            $return = ['file' => $execMethodRecord['file'], 'line' => $execMethodRecord['line']];
        }

        return $return;
    }

    /**
     * Формирует разделитель
     * @return array
     */
    public function getLogDelimiter(): array
    {
        $delimiterChar = str_repeat('=', 30);
        return [
            'start' => $delimiterChar . '[START: ' . $this->identifier . ']' . $delimiterChar . PHP_EOL,
            'end' => $delimiterChar . '[END: ' . $this->identifier . ']' . $delimiterChar . PHP_EOL,
        ];
    }

    public function useDelimiter(): bool
    {
        return $this->useDelimiter;
    }

    /**
     * @param $timerCode
     */
    public function startTimer($timerCode)
    {
        $objLoggerTimer = new LogTimer($timerCode);

        if ($this->useBacktrace) {
            $startPoint = implode(':', $this->backtrace());
            $objLoggerTimer->setStartPoint($startPoint);
        }

        $this->timers[$timerCode] = $objLoggerTimer;
    }

    /**
     * @param bool $timerCode
     * @param bool $autoStop
     * @return mixed
     */
    public function stopTimer(bool $timerCode, bool $autoStop)
    {
        if (array_key_exists($timerCode, $this->timers)) {
            $currentTimer = $this->timers[$timerCode];
            $timerData = $currentTimer->stop()->getTimerData();

            if ($autoStop) {
                $timerData['STOP_POINT'] = '__destruct';
            }

            $this->write(6, 'Lead time:', $timerData);
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool
     */
    public function __call($name, $arguments)
    {
        if ($name === 'log') {
            $name = 'info';
        }

        $logLevelCode = array_search($name, $this->logLevel);

        if ($logLevelCode !== false) {
            if ($name === 'fatal') {
                $this->sendAlert();
            }
            $this->write($logLevelCode, $arguments[0], $arguments[1]);
            return true;
        }

        return false;
    }

    /**
     * Получим список возможных способов уведомлений
     * @return array
     */
    public static function getAlertMethods(): array
    {
        $methods = [];
        $path = __DIR__ . '/alert/';
        if (is_dir($path)) {
            $list = scandir($path);
            $i = 0;
            foreach ($list as $file) {
                if ($file != '.' && $file != '..' && $file != 'logalert.php') {
                    if (is_file($path . $file)) {
                        $class = 'ZxKill\Logs\Alert\\' . basename($file, '.php');
                        if (class_exists($class)) {
                            $methods[$i]['NAME'] = $class::getName();
                            $methods[$i]['CODE'] = str_replace(['alert', '.php'], [''], $file);
                        }
                    }
                    $i++;
                }
            }
        }
        return $methods;
    }

    /**
     * Получим список возможных способов хранения логов
     * @return array
     */
    public static function getStorageMethods(): array
    {
        $methods = [];
        $path = __DIR__ . '/storage/';
        if (is_dir($path)) {
            $list = scandir($path);
            $i = 0;
            foreach ($list as $file) {
                if ($file != '.' && $file != '..' && $file != 'logstorage.php') {
                    if (is_file($path . $file)) {
                        $class = 'ZxKill\Logs\Storage\\' . basename($file, '.php');
                        if (class_exists($class)) {
                            $methods[$i]['NAME'] = $class::getName();
                            $methods[$i]['CODE'] = basename($file, '.php');
                        }
                    }
                    $i++;
                }
            }
        }
        return $methods;
    }

    public function getExecPathFile()
    {
        return $this->execPathFile;
    }

    /**
     *
     */
    public function __destruct()
    {
        if (!empty($this->timers)) {
            foreach ($this->timers as $timerCode => $objTimer) {
                if ($objTimer instanceof LogTimer && !$objTimer->isDie()) {
                    $this->stopTimer($timerCode, true);
                }
            }
        }

        $this->storage->writeInStorage();
    }
}
