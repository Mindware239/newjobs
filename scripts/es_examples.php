<?php

/**
 * Elasticsearch Usage Examples
 * This file shows how to use Elasticsearch in the application
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Services\ESService;

echo "Elasticsearch Usage Examples\n";
echo "===========================\n\n";

$esService = new ESService();

// Example 1: Index a job
echo "1. Indexing a job:\n";
echo "   \$esService->indexJob(\$jobId);\n";
echo "   This happens automatically when you create/update a job.\n\n";

// Example 2: Search jobs
echo "2. Searching jobs:\n";
echo "   \$results = \$esService->searchJobs([\n";
echo "       'q' => 'php developer',\n";
echo "       'employment_type' => 'full_time',\n";
echo "       'is_remote' => true,\n";
echo "       'salary_min' => 50000\n";
echo "   ], \$page = 1, \$perPage = 20);\n\n";

// Example 3: Search resumes
echo "3. Searching resumes:\n";
echo "   \$results = \$esService->searchResumes('python machine learning', [\n";
echo "       'employer_id' => 1,\n";
echo "       'job_id' => 5\n";
echo "   ]);\n\n";

// Example 4: Delete a job from index
echo "4. Removing a job from index:\n";
echo "   \$esService->deleteJob(\$jobId);\n";
echo "   This happens automatically when you delete a job.\n\n";

// Example 5: Create indices (one-time setup)
echo "5. Creating indices (one-time setup):\n";
echo "   \$esService->createIndices();\n";
echo "   Or run: php scripts/es_setup.php\n\n";

echo "In the Application:\n";
echo "-------------------\n";
echo "- JobsController automatically indexes jobs when created/updated\n";
echo "- ApplicationsController uses ES for resume search\n";
echo "- IndexJobWorker processes indexing in background queue\n";
echo "- All ES operations are handled by ESService class\n";

