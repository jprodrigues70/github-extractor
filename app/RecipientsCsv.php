<?php

namespace App;

use App\Contracts\CsvController;

class RecipientsCsv extends CsvController
{
    protected static $location = "/recipients-csv";

    public static function list($file, $join = false, $unique = true, $uniqueKeyPosition = 1): array
    {
        $list = parent::list($file, $join, $unique, $uniqueKeyPosition);

        return $join ? $list : array_map(function ($item) {
            return new Recipient($item[1], $item[0]);
        }, $list);
    }

    public static function listAllUniqueKeys($prefix = '', $join = false, $unique = true, $uniqueKeyPosition = 1): array
    {
        $csvs = self::scanFolder($prefix);
        $recipients = [];

        foreach ($csvs as $csv) {
            $list = parent::list($csv, $join, $unique, $uniqueKeyPosition);
            $emails = array_map(function ($item) {
                return $item[1];
            }, $list);

            $recipients = array_merge($recipients, $emails);
        }

        return $recipients;
    }
}
