<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}


$kernel = new Kernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);

print 'Seeding the Database...' . PHP_EOL;

// Drop the test database if it exists
$application->run(new ArrayInput([
    'command' => 'doctrine:database:drop',
    '--if-exists' => true,
    '--force' => true,
    '--silent' => true,
    '--env' => 'test'
]));

// Create the test database
$application->run(new ArrayInput([
    'command' => 'doctrine:database:create',
    '--silent' => true,
    '--env' => 'test'
]));

// Create the db schema
$application->run(new ArrayInput([
    'command' => 'doctrine:schema:create',
    '--silent' => true,
    '--env' => 'test'
]));

// Seed the database
$application->run(new ArrayInput([
    'command' => 'doctrine:fixtures:load',
    '--no-interaction' => true,
    '--silent' => true,
    '--env' => 'test'
]));

print 'Database seeded' . PHP_EOL;