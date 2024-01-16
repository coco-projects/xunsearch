<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch\utils;

    use Coco\xunsearch\components\XSComponent;
    use Coco\xunsearch\exception\XSException;

class XSUtils extends XSComponent
{
    public static function iniFileToArray($file): bool|array
    {
        if (!is_file($file)) {
            throw new XSException('Failed to parse project config file/string: \'' . substr($file, 0, 10) . '...\'');
        }

        return static::iniStringToArray(file_get_contents($file));
    }

    /**
     * 由于 PHP 自带的 parse_ini_file 存在一些不兼容，故自行简易实现
     *
     * @param string $iniString
     *
     * @return array
     */
    public static function iniStringToArray(string $iniString): array
    {
        $ret = [];
        $cur = &$ret;

        $lines = explode("\n", $iniString);

        foreach ($lines as $line) {
            if ($line === '' || $line[0] == ';' || $line[0] == '#') {
                continue;
            }
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($line[0] === '[' && substr($line, -1, 1) === ']') {
                $sec       = substr($line, 1, -1);
                $ret[$sec] = [];
                $cur       = &$ret[$sec];
                continue;
            }
            if (($pos = strpos($line, '=')) === false) {
                continue;
            }
            $key       = trim(substr($line, 0, $pos));
            $value     = trim(substr($line, $pos + 1), " '\t\"");
            $cur[$key] = $value;
        }

        return $ret;
    }
}
