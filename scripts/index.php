<?php

require __DIR__.'/../vendor/autoload.php';

use ListLicenses\ListLicenseCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new ListLicenseCommand());

try {
    $application->run();
} catch (Exception $e) {
    echo PHP_EOL."Unexpected exception: ".$e->getMessage().PHP_EOL;
}
