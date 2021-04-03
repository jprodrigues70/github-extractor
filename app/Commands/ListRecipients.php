<?php

namespace App\Commands;

use App\Contracts\Command;
use App\RecipientsCsv;

class ListRecipients extends Command
{
    protected $arguments = [];
    protected $options = [];

    public function handle()
    {
        $csvs = RecipientsCsv::scanFolder();

        foreach (array_values($csvs) as $csv) {
            $this->outputSuccess($csv, '- ');
            $recipients = RecipientsCsv::list($csv, true);
            if (empty($recipients)) {
                $this->output("Empty!\n", "  - ");
            } else {
                foreach ($recipients as $i => $recipient) {
                    $this->output($recipient, "  " . ((int)$i + 1) . "- ");
                }
            }
        }
    }
}
