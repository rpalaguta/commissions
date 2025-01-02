<?php

namespace Commissions\CommissionTask;

require __DIR__ . '/bootstrap.php';

use Commissions\CommissionTask\Service\CSVProcessor;

if ($argc != 2) {
    echo "Usage: php script.php input.csv\n";
    exit(1);
}

$inputFile = $argv[1];

$CSVProcessor = new CSVProcessor();

$CSVProcessor->processFile($inputFile);

?>