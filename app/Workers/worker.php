<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$queueName = $argv[1] ?? null;

if (!$queueName) {
    echo "Usage: php worker.php <queue_name>\n";
    echo "Available queues: index_job, email, webhook\n";
    exit(1);
}

$workerMap = [
    'index_job' => \App\Workers\IndexJobWorker::class,
    'email' => \App\Workers\EmailWorker::class,
    'webhook' => \App\Workers\WebhookWorker::class,
];

if (!isset($workerMap[$queueName])) {
    echo "Unknown queue: $queueName\n";
    exit(1);
}

$workerClass = $workerMap[$queueName];
$worker = new $workerClass();
$worker->run();

