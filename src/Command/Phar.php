<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Phar extends Command
{

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (in_array(ini_get("phar.readonly"), ['1', true, 'true', 'On', 'on', 'ON'])) {
            $output->writeln("环境配置不正确，请编辑" . php_ini_loaded_file() . "，修改配置项\"phar.readonly\"为Off");
            return;
        }

        try {
            $src = $input->getArgument('src');
            $name = $input->getArgument('target');
            $stub = $input->getOption('stub');
            if (!file_exists($src)) {
                throw new RuntimeException($src . '不存在');
            }
            $src = realpath($src) . DIRECTORY_SEPARATOR;
            if (!file_exists($src . $stub)) {
                throw new RuntimeException($src . $stub . '不存在');
            }
            $dirname = dirname($name);
            if (!file_exists($dirname)) {
                if (!mkdir($dirname, 0755, true)) {
                    throw new RuntimeException('创建' . $dirname . '失败');
                }
            }
            $output->writeln('Building from ' . $src . PHP_EOL . '           to ' . $name . '.phar ' . PHP_EOL . '           ............' . PHP_EOL . '           ............' . PHP_EOL . '           ............');
            $phar = new \Phar($name . '.phar');
            $phar->buildFromDirectory($src);
            $phar->setStub('#!/usr/bin/env php' . PHP_EOL . $phar->createDefaultStub($stub));
            rename($name . '.phar', $name);
            chmod($name, 0775);
            $output->writeln('Success, Please run "' . $name . '"');
        } catch (\UnexpectedValueException $e) {
            $output->writeln(PHP_EOL . 'Error, ' . $e->getMessage() . PHP_EOL);
            $output->writeln($e->getTraceAsString());
        }
    }

    protected function configure()
    {
        $this->setName('create');
        $this->setDescription('生成PHP归档文件');
        $this->addArgument('src', InputArgument::REQUIRED, 'JSON文件的目录');
        $this->addArgument('target', InputArgument::REQUIRED, 'JSON文件的目录');
        $this->addOption('stub', 's', InputOption::VALUE_OPTIONAL, '入口文件', 'src/app.php');
    }
}