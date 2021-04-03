<?php

namespace App\Commands;

use App\BlacklistCsv;
use App\Contracts\Command;
use App\Mailer;
use App\RecipientsCsv;
use App\SentlistCsv;

class Send extends Command
{
    protected $arguments = [];
    protected $options = ['-i' => 4, '-m' => 15, '-bucket' => 40, '-bucket-i' => 1800, '-bucket-m' => 3600];

    public function help()
    {
        print("Send e-mails to your recipients-csv\n\n");
        print("Usage: php mail send [options]\n\n");
        print("Example: php mail send -i=4 -m=8\n\n");
        print("Options:\n");
        $mask = "%11.9s %7.5s\t%-30s\n";
        printf($mask, "-i", "<int>", "minimum interval between 2 e-mails in seconds (default: 4s)");
        printf($mask, "-m", "<int>", "maximum interval between 2 e-mails in seconds (default: 15s)");
        printf($mask, "-bucket", "<int>", "bucket size to deliver e-mails (It's a strategy to prevent to be target as spam, default is 40)");
        printf($mask, "-bucket-i", "<int>", "minimum interval between 2 buckets in seconds (default: 1800s)");
        printf($mask, "-bucket-m", "<int>", "maximum interval between 2 buckets in seconds (default: 3500s)");
    }

    public function handle()
    {
        $blacklist = BlacklistCsv::list();
        $sentlist = SentlistCsv::list();
        $csvs = RecipientsCsv::scanFolder();

        $i = 0;
        foreach ($csvs as $csv) {
            $this->outputSuccess($csv, '- ');
            $sentPath = __DIR__ . '/../../sentlist-csv/' . $csv;
            if (!file_exists($sentPath)) {
                touch($sentPath);
            }

            $recipients = RecipientsCsv::list($csv);
            foreach ($recipients as $recipient) {
                if ($i % (int)$this->option('bucket') === 0 && $i !== 0) {
                    $wait = rand((int)$this->option('bucket-i'), (int)$this->option('bucket-m'));
                    echo "$i - VAMOS ESPERAR $wait segundos";
                    sleep($wait);
                }

                if ($i !== 0) {
                    sleep(rand((int)$this->option('i'), (int)$this->option('m')));
                }

                if (!in_array($recipient->email, $blacklist) && !in_array($recipient->email, $sentlist)) {
                    $i = $i + 1;
                    $this->output("$recipient->email", "  " . $i . "- ");
                    $mail = new Mailer();
                    $mail->to($recipient)->send();
                    SentlistCsv::put($recipient, $csv);
                }
            }
        }
    }
}
