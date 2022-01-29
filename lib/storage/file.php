<?php

namespace ZxKill\Logs\Storage;

use ZxKill\Logs\Helper\Dir;

/**
 * Class File
 */
final class File extends LogStorage
{
    /**
     * @var bool
     */
    private bool $initLogDir = false;

    /**
     * Удалим логи, которые старше, чем положено хранить
     */
    protected function rotateLog()
    {
        $logDirPath = $this->getLogDirPath();
        $rotateDay = $this->settings->ROTATE_DAY();
        if (Dir::countDirInPath($logDirPath) > $rotateDay) {
            $list = scandir($logDirPath);
            $oldPath = strtotime('-' . $rotateDay . ' day');
            foreach($list as $dr) {
                if ($dr!='.' && $dr!='..') {
                    if (is_dir($logDirPath . $dr)) {
                        if (strtotime($dr) < $oldPath) {
                            Dir::remove($logDirPath . $dr);
                        }
                    }
                }
            }
        }
    }

    /**
     * Инициализация директории для логов вида logs/{current_date}/.
     * Если директории текущего дня еще не существует, то создает.
     * @throws
     * @return bool|string
     */
    private function initLogDir()
    {
        if ($this->initLogDir === false) {
            $path = $this->getLogDirPath();
            $day = date('Y-m-d');
            $currentDayLogDir = $path . $day;
            if (file_exists($currentDayLogDir)) {
                $this->initLogDir = $currentDayLogDir;
            } else {
                $createDir = mkdir($currentDayLogDir, BX_DIR_PERMISSIONS, true);
                if ($createDir) {
                    $this->initLogDir = $currentDayLogDir;
                    $this->rotateLog();
                } else {
                    throw new \Exception('Failed to create date folder. Check root path logs dif');
                }
            }
        }

        return $this->initLogDir;
    }

    /**
     * Возвращает полный путь к папке логов.
     * @return string
     */
    private function getLogDirPath(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->settings->LOG_DIR();
    }

    /**
     * Метод возвращает имя файла основываясь на свойство $this->code, которое устанавливается в конструкторе
     * и расширения (задается в файле настроек).
     * @return string
     */
    private function getLogFileName(): string
    {
        if ($this->settings->LOG_FILE_EXTENSION()) {
            $name = $this->objLog->getLoggerCode() . $this->settings->LOG_FILE_EXTENSION();
        } else {
            $name = $this->objLog->getLoggerCode() . '.txt';
        }

        return $name;
    }

    /**
     * Возвращает путь к директории, в которую нужно положить файл.
     * @return mixed|string
     * @throws \Exception
     */
    private function getLogDir(): string
    {
        $path = $this->initLogDir() . '{space}' ;
        $path = str_replace('{space}', '/', $path);

        return $path;
    }

    /**
     * Записывает сообщения лога в файл.
     */
    public function writeInStorage()
    {
        if (!empty($this->objLog->getLogDataItems())) {
            $pathFile = '';
            $strLogData = implode('', $this->objLog->getLogDataItems());

            if ($this->objLog->useDelimiter()) {
                $dl = $this->objLog->getLogDelimiter();
                $strLogData = $dl['start'] . $strLogData . $dl['end'];
            }

            $canWrite = true;

            try {
                $pathFile = $this->getLogDir() . $this->getLogFileName();
            } catch (\Exception $e) {
                //Если есть проблема с инициализацией папки, не пишем. Письма отправим.
                $canWrite = false;
            }

            if ($canWrite) {
                file_put_contents($pathFile, $strLogData, FILE_APPEND);
            }

            if ($this->objLog->needSendAlert()) {
                $class = 'ZxKill\Logs\Alert\Alert' . ucfirst($this->settings->HANDLER_EVENT());
                if (class_exists($class)) {
                    $objLogAlert = new $class($this->objLog);
                    $objLogAlert->send();
                }
            }
        }
    }

    public static function getName(): string
    {
        return 'Файлы';
    }
}
