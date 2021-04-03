<?php

namespace App;

use App\Contracts\CsvController;

class RecipientsCsv extends CsvController
{
    protected static $location = "/recipients-csv";

    public static function list($file, $join = false, $unique = true, $emailKey = 1): array
    {
        $list = parent::list($file, $join, $unique, $emailKey);

        return $join ? $list : array_map(function ($item) {
            return new Recipient($item[1], $item[0]);
        }, $list);
    }
}
