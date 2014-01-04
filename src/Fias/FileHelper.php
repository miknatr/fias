<?php

namespace Fias;

class FileHelper
{
    public static function isItReadable($path)
    {
        if (!is_readable($path)) {
            throw new FileException('Путь недоступен для чтения: ' . $path);
        }

        return true;
    }

    public static function isItWritable($path)
    {
        if (!is_writable($path)) {
            throw new FileException('Путь недоступен для записи: ' . $path);
        }

        return true;
    }

    public static function isFolder($path)
    {
        if (!is_dir($path)) {
            throw new FileException('Не является директорией: ' . $path);
        }
    }
}
