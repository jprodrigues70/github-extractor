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
    protected $options = ['-i' => 4, '-m' => 15, '-pfx' => 'users-', '-bucket' => 40, '-bucket-i' => 1800, '-bucket-m' => 3600];

    public function help()
    {
        print("Send e-mails to your recipients-csv\n\n");
        print("Usage: php mail send [options]\n\n");
        print("Example: php mail send -i=4 -m=8\n\n");
        print("Options:\n");
        $mask = "%11.9s %7.5s\t%-30s\n";
        printf($mask, "-pfx", "<str>", "recipient file prefix (default: users-)");
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
        $csvs = RecipientsCsv::scanFolder($this->option('-pfx'));

        $i = 0;
        foreach ($csvs as $csv) {
            $this->outputSuccess($csv, '- ');
            $sentPath = __DIR__ . '/../../sentlist-csv/' . $csv;
            $recipientPath = __DIR__ . '/../../recipients-csv/' . $csv;
            if (!file_exists($sentPath)) {
                touch($sentPath);
            }

            $recipients = RecipientsCsv::list($csv);
            foreach ($recipients as $recipient) {
                if ($i === 300) {
                    print("\n\nYou can't send more than 300 emails per day\n\n");
                    die();
                }

                if ($i % (int)$this->option('-bucket') === 0 && $i !== 0) {
                    $wait = rand((int)$this->option('-bucket-i'), (int)$this->option('-bucket-m'));
                    echo "\nBucket completed! {$wait} to the next email\n\n";
                    sleep($wait);
                }


                if (!in_array($recipient->email, $blacklist) && !in_array($recipient->email, $sentlist)) {

                    if ($i !== 0) {
                        $wait = rand((int)$this->option('-i'), (int)$this->option('-m'));
                        echo "     {$wait}s to the next email ";
                        for ($j = 0; $j < $wait; $j++) {
                            echo ".";
                            sleep(1);
                        }
                        echo "\n";
                    }

                    $i = $i + 1;
                    $this->output("$recipient->email", "  " . $i . "- ");
                    $mail = new Mailer();
                    $mail->to($recipient)->send();
                    SentlistCsv::put($recipient, $csv);
                }
            }
            rename($recipientPath, str_replace('.csv', '.txt', $recipientPath));
        }
    }
}
