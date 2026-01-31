<?php

/**
 * Test Elasticsearch Connection
 * Run this script to verify your Elasticsearch setup
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use Elasticsearch\ClientBuilder;

echo "Testing Elasticsearch Connection...\n\n";

$host = $_ENV['ES_HOST'] ?? 'localhost';
$port = (int)($_ENV['ES_PORT'] ?? 9200);
$username = $_ENV['ES_USERNAME'] ?? null;
$password = $_ENV['ES_PASSWORD'] ?? null;

echo "Connecting to: $host:$port\n";

$hosts = [
    [
        'host' => $host,
        'port' => $port,
        'scheme' => 'http'
    ]
];

$clientBuilder = ClientBuilder::create()->setHosts($hosts);

if ($username) {
    $clientBuilder->setBasicAuthentication($username, $password);
    echo "Using authentication\n";
}

try {
    $client = $clientBuilder->build();
    
    // Test connection
    $response = $client->info();
    
    echo "\n✓ Connection successful!\n\n";
    echo "Cluster Name: " . $response['cluster_name'] . "\n";
    echo "Version: " . $response['version']['number'] . "\n";
    echo "Elasticsearch is ready to use!\n\n";
    
    // Check existing indices
    $indices = $client->cat()->indices(['format' => 'json']);
    echo "Existing indices:\n";
    if (empty($indices)) {
        echo "  (none)\n";
        echo "\nRun: php scripts/es_setup.php to create indices\n";
    } else {
        foreach ($indices as $index) {
            echo "  - " . $index['index'] . " (" . $index['docs.count'] . " documents)\n";
        }
    }
    
} catch (\Exception $e) {
    echo "\n✗ Connection failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "1. Make sure Elasticsearch is running\n";
    echo "2. Check ES_HOST and ES_PORT in .env file\n";
    echo "3. For Docker: docker-compose up -d elasticsearch\n";
    echo "4. Test manually: curl http://localhost:9200\n";
    exit(1);
}

