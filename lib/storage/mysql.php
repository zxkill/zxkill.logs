<?php

namespace ZxKill\Logs\Storage;

/**
 * Class MySql
 */
final class MySql extends LogStorage
{

    /**
     * Удалим логи, которые старше, чем положено хранить
     */
    protected function rotateLog()
    {
        //todo:
    }

    /**
     * Записывает сообщения лога в хранилище
     */
    public function writeInStorage()
    {
        //todo:
    }

    public static function getName(): string
    {
        return 'MySQL';
    }
}
