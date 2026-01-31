<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Candidates') ?> - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/../../layouts/employer/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Candidates for: <?= htmlspecialchars($job['title'] ?? 'Job') ?></h1>
            <button onclick="generateScores()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 shadow-md">
                Generate AI Scores
            </button>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-gray-500">No candidates found for this job.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4 flex-1">
                                <!-- Profile Picture -->
                                <img src="<?= htmlspecialchars($candidate['profile_picture'] ?? '/images/default-avatar.png') ?>" 
                                     alt="<?= htmlspecialchars($candidate['full_name'] ?? 'Candidate') ?>"
                                     class="w-16 h-16 rounded-full object-cover">

                                <div class="flex-1">
                                    <!-- Name and Location -->
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-xl font-semibold text-gray-900">
                                            <?= htmlspecialchars($candidate['full_name'] ?? 'Unknown') ?>
                                        </h3>
                                        <?php if (!empty($candidate['city']) || !empty($candidate['state'])): ?>
                                            <span class="text-gray-500">
                                                <?= htmlspecialchars(trim(($candidate['city'] ?? '') . ', ' . ($candidate['state'] ?? ''), ', ')) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Match Score Badge -->
                                    <div class="flex items-center space-x-4 mb-3">
                                        <?php
                                        $score = (int)($candidate['overall_match_score'] ?? 0);
                                        $scoreColor = 'bg-green-100 text-green-800';
                                        if ($score < 50) {
                                            $scoreColor = 'bg-red-100 text-red-800';
                                        } elseif ($score < 70) {
                                            $scoreColor = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($score < 85) {
                                            $scoreColor = 'bg-purple-100 text-purple-800';
                                        }
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $scoreColor ?>">
                                            Match: <?= $score ?>%
                                        </span>

                                        <!-- Recommendation Badge -->
                                        <?php if (!empty($candidate['recommendation'])): ?>
                                            <?php
                                            $rec = $candidate['recommendation'];
                                            $recColor = 'bg-gray-100 text-gray-800';
                                            if ($rec === 'Strong Hire') {
                                                $recColor = 'bg-green-600 text-white';
                                            } elseif ($rec === 'Shortlist') {
                                                $recColor = 'bg-purple-600 text-white';
                                            } elseif ($rec === 'Review') {
                                                $recColor = 'bg-yellow-100 text-yellow-800';
                                            }
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $recColor ?>">
                                                <?= htmlspecialchars($rec) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Score Breakdown -->
                                    <div class="flex items-center space-x-6 text-sm text-gray-600 mb-3">
                                        <span>Skills: <strong><?= $candidate['skill_score'] ?? 0 ?>%</strong></span>
                                        <span>Experience: <strong><?= $candidate['experience_score'] ?? 0 ?>%</strong></span>
                                        <span>Education: <strong><?= $candidate['education_score'] ?? 0 ?>%</strong></span>
                                    </div>

                                    <!-- Matched Skills -->
                                    <?php if (!empty($candidate['matched_skills']) && is_array($candidate['matched_skills'])): ?>
                                        <div class="mb-3">
                                            <span class="text-sm text-gray-600">Matched Skills: </span>
                                            <div class="inline-flex flex-wrap gap-2 mt-1">
                                                <?php foreach (array_slice($candidate['matched_skills'], 0, 5) as $skill): ?>
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                                        <?= htmlspecialchars($skill) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count($candidate['matched_skills']) > 5): ?>
                                                    <span class="text-xs text-gray-500">+<?= count($candidate['matched_skills']) - 5 ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Missing Skills -->
                                    <?php if (!empty($candidate['missing_skills']) && is_array($candidate['missing_skills'])): ?>
                                        <div class="mb-3">
                                            <span class="text-sm text-gray-600">Missing: </span>
                                            <div class="inline-flex flex-wrap gap-2 mt-1">
                                                <?php foreach (array_slice($candidate['missing_skills'], 0, 3) as $skill): ?>
                                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">
                                                        <?= htmlspecialchars($skill) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- AI Summary -->
                                    <?php if (!empty($candidate['summary'])): ?>
                                        <p class="text-sm text-gray-700 italic mt-2">
                                            "<?= htmlspecialchars($candidate['summary']) ?>"
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col space-y-2 ml-4">
                                <?php if (!empty($candidate['resume_url'])): ?>
                                    <a href="<?= htmlspecialchars($candidate['resume_url']) ?>" 
                                       target="_blank"
                                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center text-sm">
                                        View Resume
                                    </a>
                                <?php endif; ?>
                                <a href="/employer/candidates/<?= $candidate['id'] ?>" 
                                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 text-center text-sm">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function generateScores() {
            if (!confirm('Generate AI scores for all candidates? This may take a few minutes.')) {
                return;
            }

            const jobId = <?= $job['id'] ?? 0 ?>;
            const button = event.target;
            button.disabled = true;
            button.textContent = 'Generating...';

            try {
                const response = await fetch(`/employer/jobs/${jobId}/generate-scores`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(`Successfully generated scores for ${data.results.success} candidates.`);
                    location.reload();
                } else {
                    alert('Failed to generate scores: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                button.disabled = false;
                button.textContent = 'Generate AI Scores';
            }
        }
    </script>
</body>
</html>

