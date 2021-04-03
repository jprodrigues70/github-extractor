<?php

namespace App;

use App\Contracts\CsvController;

class SentlistCsv extends CsvController
{
    protected static $location = "/sentlist-csv";

    public static function list($file = '', $join = false, $unique = true, $emailKey = 0): array
    {
        $csvs = self::scanFolder();
        $sent = [];

        foreach ($csvs as $csv) {
            $list = parent::list($csv, $join, $unique, $emailKey);
            $emails = array_map(function ($item) {
                return $item[0];
            }, $list);

            $sent = array_merge($sent, $emails);
        }

        return $sent;
    }

    public static function put($recipient, $file): void
    {
        date_default_timezone_set('America/Sao_Paulo');
        $line[] = $recipient->email;
        $line[] = date('Y-m-d H:i:s');
        parent::put($line, $file);
    }
}
