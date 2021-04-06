<?php

namespace App\Commands;

use App\BlacklistCsv;
use App\Contracts\Command;
use App\RecipientsCsv;
use App\SentlistCsv;
use GuzzleHttp\Client;

class ExploreGithub extends Command
{
    protected $arguments = [];
    protected $options = ['-m' => 1000];
    private $fileName = '';
    private $sentPath =  __DIR__ . '/../../recipients-csv';
    private $total = 0;
    private $founds = 0;
    public function help()
    {
        print("Get e-mails from Github and create recipients-csv\n\n");
        print("Usage: php mail explore-githun [options]\n\n");
        print("Example: php mail send -m=500\n\n");
        print("Options:\n");
        $mask = "%11.9s %7.5s\t%-30s\n";

        printf($mask, "-m", "<int>", "minimum total of emails (default: 1000)");
    }

    public function handle()
    {
        $items = RecipientsCsv::scanFolder();

        $items = array_map(function ($item) {
            $value = str_replace('.csv', '', $item);
            return is_numeric($value) ? (int)$value : null;
        }, $items);
        $items = array_filter($items, function ($item) {
            return $item;
        });

        sort($items);

        if (count($items)) {
            $lastItem = str_replace('.csv', '', end($items));
            if (is_numeric($lastItem)) {
                $lastItem = (int)$lastItem;
            }
        } else {
            $lastItem = 0;
        }
        $this->fileName = $lastItem + 1;
        $searchUri = env('GITHUB_SEARCH');

        $page = 1;

        preg_match_all("/&p=[0-9]+/", $searchUri, $matches);

        if (count($matches[0])) {
            $pageString = end(end($matches));
            $page = end(explode('=', $pageString));
        }
        $uri = preg_replace("/&p=[0-9]+/", '', $searchUri);
        echo "$uri\n\n";
        $this->search($uri, $page);
    }

    private function search($uri, $page)
    {
        echo "Searching e-mails on page $page\n";
        $http = new Client(['base_uri' => 'https://api.github.com', 'headers' => ['Authorization' => 'token ' . env('GITHUB_TOKEN')]]);
        $result = $http->get("/search/users$uri" . "&per_page=100&page=$page");
        $content = json_decode($result->getBody()->getContents());
        $link = $result->getHeader('link');
        preg_match_all("/&page=[0-9]+/", $link[0], $matches);
        $pages = end($matches);
        $lastPage = $page > 1 && count($pages) === 2 ? $page : $pages[min(2, count($pages) - 1)];
        $lastPage = str_replace("&page=", '', $lastPage);

        $nextPage = (int)$page < (int)$lastPage ? (int)$page + 1 : null;
        $path = "{$this->sentPath}/{$this->fileName}";
        $j = 0;
        $sentlist = SentlistCsv::list();
        $blacklist = BlacklistCsv::list();

        foreach ($content->items as $item) {
            if (!empty($item->url)) {
                $profileResult = $http->get($item->url);
                $profileContent = json_decode($profileResult->getBody()->getContents());

                if (!empty($profileContent->email)) {
                    $this->founds = $this->founds + 1;
                    $line = [];
                    $nameParts = explode(' ', $profileContent->name);
                    $line[] = trim($nameParts[0]);
                    $line[] = $profileContent->email;
                    $line[] = $profileContent->name;
                    $line[] = $profileContent->html_url;


                    if (!file_exists($path)) {
                        touch($path);
                    }

                    $recipients = array_map(function ($item) {
                        return $item->email;
                    }, RecipientsCsv::list($this->fileName));

                    $recipients = array_merge($recipients, RecipientsCsv::listAllEmails());

                    if (!in_array($profileContent->email, $recipients) && !in_array($profileContent->email, $sentlist) && !in_array($profileContent->email, $blacklist)) {
                        RecipientsCsv::put($line, $this->fileName);
                        $this->total = $this->total + 1;
                        echo "!";
                        $j++;
                    } else {
                        echo ":";
                    }
                } else {
                    echo ".";
                }
            }
        }

        $this->outputSuccess("Found: $j new emails\n", "  ");

        if (!empty($nextPage) && $this->total < (int)$this->option('-m')) {
            $wait = rand(5, 10);
            print("\n{$wait}s to page $nextPage ");
            for ($i = 0; $i < $wait; $i++) {
                echo '.';
                sleep(1);
            }
            echo "\n\n";
            $this->search($uri, $nextPage);
        } else if ($this->total) {
            $this->outputSuccess("Total: {$this->total} new emails on {$this->fileName}.csv from {$this->founds} found!", "\n");
            rename($path, "$path.csv");
        } else {
            $this->outputSuccess("Total: 0 new emails from {$this->founds} found!", "\n");
        }
        echo "\n{$content->total_count} results\n";
    }
}
