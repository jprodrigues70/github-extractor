<?php

namespace App\Commands;

use App\Contracts\GithubSearchCommand;

class GithubUsers extends GithubSearchCommand
{
    protected $arguments = [];
    protected $options = ['-m' => 1000, '-rc-key' => 'email', '-bl-key' => 'email', '-pfx' => 'users-'];
    protected $fileName = '';
    protected $sentPath =  __DIR__ . '/../../recipients-csv';
    protected $total = 0;
    protected $founds = 0;
    protected $target = 'emails';
    protected $searchUriPrefix = '/search/users';

    public function help()
    {
        print("Get {$this->target} from Github and create recipients-csv\n\n");
        print("Usage: php mail github-users [options]\n\n");
        print("Example: php mail github-users -m=500\n\n");
        print("Options:\n");
        $mask = "%11.9s %7.5s\t%-30s\n";

        printf($mask, "-m", "<int>", "minimum total of {$this->target} (default: 1000)");
        printf($mask, "-rc-key", "<str>", "recipient unique key (default: email)");
        printf($mask, "-bl-key", "<str>", "blacklist-related key (default: email)");
        printf($mask, "-pfx", "<str>", "recipient file prefix (default: users-)");
    }

    protected function mountLine(object $model): array
    {
        $line = [];
        $nameParts = explode(' ', $model->name);
        $line[] = trim($nameParts[0]);
        $line[] = $model->email;
        $line[] = $model->name;
        $line[] = $model->html_url;
        return $line;
    }
}
