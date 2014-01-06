<?php

namespace Fias;

class FileHelper
{
    public static function checkReadable($path)
    {
        if (!is_readable($path)) {
            throw new FileException('Путь недоступен для чтения: ' . $path);
        }
    }

    public static function checkWritable($path)
    {
        if (!is_writable($path)) {
            throw new FileException('Путь недоступен для записи: ' . $path);
        }
    }

    public static function checkThatIsDirectory($path)
    {
        if (!is_dir($path)) {
            throw new FileException('Не является директорией: ' . $path);
        }
    }
}
