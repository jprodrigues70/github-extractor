<?php

namespace App;

use App\Contracts\CsvController;

class BlacklistCsv extends CsvController
{
    protected static $location = "/blacklist-csv";

    public static function list($file = 'blacklist.csv', $join = false, $unique = true, $emailKey = 0): array
    {
        $list = parent::list($file, $join, $unique, $emailKey);

        return $join ? $list : array_map(function ($item) {
            return $item[0];
        }, $list);
    }
}
