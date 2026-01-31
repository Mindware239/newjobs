<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Jobs for You') ?> - Mindware Infotech</title>
    <meta name="description" content="Find your dream job with Mindware Infotech. Browse our latest job listings and apply today!">
    <meta name="keywords" content="jobs, job listings, job search, Mindware Infotech">
    <meta name="author" content="Mindware Infotech">
    <link rel="canonical" href="<?= htmlspecialchars($job['url'] ?? '') ?>">
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php $base = $base ?? '/'; require __DIR__ . '/../../include/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8"><?= htmlspecialchars($title ?? 'Jobs for You') ?></h1>

        <!-- Recommended Jobs Section -->
        <?php if (!empty($recommendedJobs)): ?>
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Recommended for You</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($recommendedJobs as $job): ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <!-- Match Badge -->
                            <?php
                            $score = (int)($job['overall_match_score'] ?? 0);
                            $scoreColor = 'bg-blue-100 text-blue-800';
                            $scoreText = 'Strong Match';
                            if ($score < 50) {
                                $scoreColor = 'bg-red-100 text-red-800';
                                $scoreText = 'Low Match';
                            } elseif ($score < 70) {
                                $scoreColor = 'bg-yellow-100 text-yellow-800';
                                $scoreText = 'Fair Match';
                            } elseif ($score < 85) {
                                $scoreColor = 'bg-blue-100 text-blue-800';
                                $scoreText = 'Good Match';
                            }
                            ?>
                            <div class="flex justify-between items-start mb-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $scoreColor ?>">
                                    <?= $score ?>% Match - <?= $scoreText ?>
                                </span>
                                <?php if ($job['recommendation'] === 'Strong Hire'): ?>
                                    <span class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-medium">
                                        ⭐ Top Pick
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Job Title -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>" class="hover:text-blue-600">
                                    <?= htmlspecialchars($job['title'] ?? 'Untitled Job') ?>
                                </a>
                            </h3>

                            <!-- Company -->
                            <?php if (!empty($job['company_name'])): ?>
                                <p class="text-gray-600 mb-2">
                                    <?= htmlspecialchars($job['company_name']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Location -->
                            <?php if (!empty($job['location'])): ?>
                                <p class="text-gray-500 text-sm mb-2">
                                    📍 <?= htmlspecialchars($job['location']) ?>
                                    <?php if (!empty($job['is_remote']) && $job['is_remote']): ?>
                                        <span class="text-blue-600">(Remote)</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <!-- Salary -->
                            <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                <p class="text-gray-700 font-medium mb-2">
                                    ₹<?= number_format($job['salary_min'] ?? 0) ?> - 
                                    ₹<?= number_format($job['salary_max'] ?? 0) ?> 
                                    <?= htmlspecialchars($job['currency'] ?? 'INR') ?>
                                </p>
                            <?php endif; ?>

                            <!-- Match Summary -->
                            <?php if (!empty($job['match_summary'])): ?>
                                <p class="text-sm text-gray-600 italic mb-3">
                                    "<?= htmlspecialchars(substr($job['match_summary'], 0, 120)) ?>..."
                                </p>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex space-x-2 mt-4">
                                <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>" 
                                   class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center text-sm">
                                    View Details
                                </a>
                                <button onclick="applyJob('<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>')" 
                                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                                    Apply Now
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow p-8 text-center mb-8">
                <p class="text-gray-500 mb-4">No job recommendations available yet.</p>
                <p class="text-sm text-gray-400">Complete your profile and upload your resume to get personalized job recommendations.</p>
            </div>
        <?php endif; ?>

        <!-- Trending/Hot Jobs Section -->
        <?php if (!empty($trendingJobs)): ?>
            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">🔥 Hot Jobs (Trending)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($trendingJobs as $job): ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <!-- Hot Badge -->
                            <div class="flex justify-between items-start mb-3">
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                    🔥 Hot Job
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?= $job['application_count'] ?? 0 ?> applications
                                </span>
                            </div>

                            <!-- Job Title -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>" class="hover:text-blue-600">
                                    <?= htmlspecialchars($job['title'] ?? 'Untitled Job') ?>
                                </a>
                            </h3>

                            <!-- Company -->
                            <?php if (!empty($job['company_name'])): ?>
                                <p class="text-gray-600 mb-2">
                                    <?= htmlspecialchars($job['company_name']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Location -->
                            <?php if (!empty($job['location'])): ?>
                                <p class="text-gray-500 text-sm mb-2">
                                    📍 <?= htmlspecialchars($job['location']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Salary -->
                            <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                <p class="text-gray-700 font-medium mb-3">
                                    ₹<?= number_format($job['salary_min'] ?? 0) ?> - 
                                    ₹<?= number_format($job['salary_max'] ?? 0) ?> 
                                    <?= htmlspecialchars($job['currency'] ?? 'INR') ?>
                                </p>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex space-x-2 mt-4">
                                <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>" 
                                   class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center text-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <script>
        async function applyJob(jobSlug) {
            // Implement job application logic
            window.location.href = `/candidate/jobs/${jobSlug}/apply`;
        }
    </script>
       <?php
require __DIR__ . '/../../include/footer.php';
?>
</body>
</html>

