<?php

namespace FileSystem;

class FileHelper
{
    public static function ensureIsReadable($path)
    {
        if (!is_readable($path)) {
            throw new FileException('Путь недоступен для чтения: ' . $path);
        }
    }

    public static function ensureIsWritable($path)
    {
        if (!is_writable($path)) {
            throw new FileException('Путь недоступен для записи: ' . $path);
        }
    }

    public static function ensureIsDirectory($path)
    {
        if (!is_dir($path)) {
            throw new FileException('Не является директорией: ' . $path);
        }
    }
}
