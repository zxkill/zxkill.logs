<?php

namespace ZxKill\Logs\Helper;

class Dir
{
    /**
     * Считаем количество подпапок в папке
     * @param $path
     * @return int
     */
    public static function countDirInPath($path): int
    {
        $i = 0;
        $list = scandir($path);
        foreach($list as $dr) {
            if ($dr != '.' && $dr != '..') {
                if (is_dir($path . '/' . $dr)) $i++;
            }
        }
        return $i;
    }

    /**
     * Удаляем папку со всем содержимым
     * @param $path
     */
    public static function remove($path) {
        if (is_file($path)) {
            @unlink($path);
        } else {
            array_map([__CLASS__, 'remove'], glob($path . '/*')) == @rmdir($path);
        }
        @rmdir($path);
    }
}
