<?php

namespace App\Contracts;

use App\BlacklistCsv;
use App\RecipientsCsv;
use App\SentlistCsv;
use GuzzleHttp\Client;

abstract class GithubSearchCommand extends Command
{
    protected $arguments = [];
    protected $options = ['-m' => 1000, '-rc-key' => 'email', '-bl-key' => 'email', '-pfx' => ''];
    protected $fileName = '';
    protected $sentPath =  __DIR__ . '/../../recipients-csv';
    protected $total = 0;
    protected $founds = 0;
    protected $target = 'emails';
    protected $searchUriPrefix = '/search/users';

    public function handle()
    {
        $items = RecipientsCsv::scanFolder($this->option('-pfx'));

        $items = array_map(function ($item) {
            $value = str_replace('.csv', '', $item);
            $value = str_replace($this->option("-pfx"), '', $value);

            return is_numeric($value) ? (int)$value : null;
        }, $items);

        $items = array_filter($items, function ($item) {
            return $item;
        });

        sort($items);

        $lastItem = !empty($items) ? end($items) : 0;

        $this->fileName = $this->option("-pfx") . ($lastItem + 1);

        $searchUri = env('GITHUB_SEARCH');

        $page = 1;

        preg_match_all("/&p=[0-9]+/", $searchUri, $matches);

        if (count($matches[0])) {
            $pageString = end(end($matches));
            $page = end(explode('=', $pageString));
        }
        $uri = preg_replace("/&p=[0-9]+/", '', $searchUri);

        $this->output("$uri\n");
        $this->search($uri, $page);
    }

    protected abstract function mountLine(object $model): array;

    private function search($uri, $page)
    {
        $this->output("Searching {$this->target} on page $page");
        $http = new Client(['base_uri' => 'https://api.github.com', 'headers' => ['Authorization' => 'token ' . env('GITHUB_TOKEN')]]);
        $result = $http->get($this->searchUriPrefix . $uri . "&per_page=100&page=$page");

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

        foreach ($content->items as $item) {
            if (!empty($item->url)) {
                $modelResult = $http->get($item->url);
                $model = json_decode($modelResult->getBody()->getContents());
                $rcKey = $this->option('-rc-key');

                if (!empty($model->{$rcKey})) {
                    $this->founds = $this->founds + 1;

                    $line = $this->mountLine($model);

                    if (!file_exists($path)) {
                        touch($path);
                    }

                    $recipients = array_map(function ($item) use ($rcKey) {
                        return $item->{$rcKey};
                    }, RecipientsCsv::list($this->fileName));

                    $recipients = array_merge($recipients, RecipientsCsv::listAllUniqueKeys($this->option('-pfx')));

                    if (!in_array($model->{$rcKey}, $recipients) && !in_array($model->{$rcKey}, $sentlist)) {
                        if (!$this->inBlacklist($model)) {
                            RecipientsCsv::put($line, $this->fileName);
                            $this->total = $this->total + 1;
                            echo "{$this->SUCCESS}!{$this->ENDC}";
                            $j++;
                        } else {
                            echo "{$this->FAIL}*{$this->ENDC}";
                        }
                    } else {
                        echo ":";
                    }
                } else {
                    echo ".";
                }
            }
        }

        $this->outputSuccess("Found: $j new {$this->target}\n", "  ");

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
            $this->outputSuccess("Total: {$this->total} new {$this->target} on {$this->fileName}.csv from {$this->founds} found!", "\n");
            rename($path, "$path.csv");
            echo "\n{$content->total_count} results\n";
        } else {
            $this->outputSuccess("Total: 0 new {$this->target} from {$this->founds} found!", "\n");
            echo "\n{$content->total_count} results\n";
        }
    }

    /**
     * Check if item is in blacklist
     *
     * @param object $line
     * @return boolean
     */
    private function inBlacklist(object $line): bool
    {
        $blacklist = BlacklistCsv::list();
        return in_array($line->{$this->option('-bl-key')}, $blacklist);
    }
}
