<?php

namespace App\Contracts;

use Exception;

abstract class Command
{
    protected $argumentsStack = [];
    protected $optionsStack = [];
    protected $arguments = [];
    protected $options = [];
    protected $deprecated = null;

    protected $SUCCESS = "\033[32m";
    protected $INFO = "\033[93m";
    protected $ENDC = "\033[0m";
    protected $FAIL = "\033[31m";

    public function __construct($name, ...$params)
    {
        if (method_exists($this, 'handle')) {
            $this->config($params);
            $this->prepare(...$params);
            if ($this->deprecated) {
                $this->outputFail("\n{$this->deprecated}\n");
            }
            if (in_array('--help', $params)) {
                $this->help();
                die();
            }

            $startTime = microtime(true);

            $this->{'handle'}(...$params);

            $runTime = round(microtime(true) - $startTime, 2);

            print("\n\n{$this->INFO}Finish:{$this->ENDC}  {$name} ({$runTime} seconds)\n");

            $this->afterHandle(...$params);
        } else {
            throw new Exception('You have to set a handle method in Command class');
        }
    }

    private function config(array $params): void
    {
        $i = 0;

        foreach ($params as $param) {
            $arr = explode('=', $param);

            if (count($arr) === 2) {
                if (in_array($arr[0], array_keys($this->options))) {
                    $this->optionsStack[$arr[0]] = $arr[1];
                } else {
                    throw new Exception("The option {$arr[0]} is invalid");
                }
            } else {
                $this->argumentsStack[$this->arguments[$i]] = $param;
            }
        }
        $this->optionsStack = array_merge($this->options, $this->optionsStack);
    }

    public function option(string $name): string
    {
        return $this->optionsStack[$name] ?? '';
    }

    public function argument(string $name): string
    {
        return $this->argumentsStack[$name] ?? '';
    }

    public function prepare()
    {
    }

    public function afterHandle()
    {
    }

    public function help()
    {
    }

    protected function output($message, $prefix = ''): void
    {
        print("$prefix{$message}\n");
    }

    protected function outputSuccess($message, $prefix = '')
    {
        print("{$this->SUCCESS}$prefix{$message}{$this->ENDC}\n");
    }

    protected function outputFail($message, $prefix = '')
    {
        print("{$this->FAIL}$prefix{$message}{$this->ENDC}\n");
    }
}
