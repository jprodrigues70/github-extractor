<?php

namespace App\Commands;

use App\Contracts\GithubSearchCommand;
use GuzzleHttp\Client;

class GithubRepos extends GithubSearchCommand
{
    protected $arguments = [];
    protected $options = ['-m' => 1000, '-rc-key' => 'id', '-bl-key' => 'id', '-pfx' => 'repos-'];
    protected $fileName = '';
    protected $sentPath =  __DIR__ . '/../../recipients-csv';
    protected $total = 0;
    protected $founds = 0;
    protected $target = 'repos';
    protected $searchUriPrefix = '/search/repositories';

    public function help()
    {
        print("Get {$this->target} from Github and create recipients-csv\n\n");
        print("Usage: php mail github-repos [options]\n\n");
        print("Example: php mail github-repos -m=500\n\n");
        print("Options:\n");
        $mask = "%11.9s %7.5s\t%-30s\n";

        printf($mask, "-m", "<int>", "minimum total of {$this->target} (default: 1000)");
        printf($mask, "-rc-key", "<str>", "recipient unique key (default: id)");
        printf($mask, "-bl-key", "<str>", "blacklist-related key (default: id)");
        printf($mask, "-pfx", "<str>", "recipient file prefix (default: repos-)");
    }

    protected function mountLine(object $model): array
    {
        $http = new Client(['headers' => ['Authorization' => 'token ' . env('GITHUB_TOKEN')]]);
        $result = $http->get($model->owner->url);
        $owner = json_decode($result->getBody()->getContents());

        $line = [];
        $line[] = $model->full_name;
        $line[] = $model->id;
        $line[] = $model->name;
        $line[] = $model->html_url;
        $line[] = $model->language;

        $nameParts = explode(' ', $owner->name);

        $line[] = trim($nameParts[0]);
        $line[] = $owner->email;
        $line[] = $owner->name;
        $line[] = $owner->html_url;

        return $line;
    }
}
