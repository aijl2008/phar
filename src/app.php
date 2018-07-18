<?php
require __DIR__ . '/../vendor/autoload.php';
$Application = new \Symfony\Component\Console\Application();
$Application->add(new \App\Command\Phar());
$Application->run();
