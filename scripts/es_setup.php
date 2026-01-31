<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Services\ESService;

echo "Setting up Elasticsearch indices...\n";

$esService = new ESService();
$esService->createIndices();

echo "Indices created successfully!\n";

