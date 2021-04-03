<?php

namespace App\Contracts;

use Exception;

class CsvController
{
    protected static $location;

    public static function getLocation(): string
    {
        $location = str_replace('/', '', static::$location);
        return __DIR__ . "/../../{$location}";
    }

    public static function scanFolder(): array
    {
        $location = static::getLocation();

        if (!is_dir($location)) {
            throw new Exception("Folder not found!");
        }

        $csvs = array_filter(scandir($location), function ($item) {
            $arr = explode('.', $item);
            return count($arr) >= 2 && strtolower(end($arr)) === 'csv';
        });

        return $csvs;
    }

    public static function list($file, $join = false, $unique = true, $emailKey = 1): array
    {
        $list = [];
        $location = static::getLocation();

        if (($handle = fopen("$location/$file", "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                $list[] = $join ? implode(', ', $data) : $data;
            }
        }

        return $unique ? array_values(self::unique(!$join, $list, $emailKey)) : $list;
    }

    private static function unique($multidimensional = 0, $array, $key): array
    {
        if (!$multidimensional) {
            return array_unique($array);
        }

        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function put($line, $file): void
    {
        $location = static::getLocation();
        $fp = fopen("$location/$file", 'a');
        fputcsv($fp, $line);
        fclose($fp);
    }
}
