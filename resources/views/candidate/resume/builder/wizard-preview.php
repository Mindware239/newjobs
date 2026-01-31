<?php
// Resume preview matching ResumeNow style
$headerSection = $sectionsData['header'] ?? null;
$headerContent = $headerSection['section_data']['content'] ?? [];
$colors = $template ? ($template->getSchema()['colors'] ?? []) : [];
$primaryColor = $colors['primary'] ?? '#2563eb';
// Theme override (from 'theme' section)
$themeSection = $sectionsData['theme'] ?? null;
if ($themeSection && !empty($themeSection['section_data']['content']['primary_color'])) {
    $primaryColor = $themeSection['section_data']['content']['primary_color'];
}

// Get full name or split
$fullName = $headerContent['full_name'] ?? 'YOUR NAME';
$initials = '';
if ($fullName && $fullName !== 'YOUR NAME') {
    $nameParts = explode(' ', $fullName);
    $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
} else {
    $initials = 'YN';
}

$email = $headerContent['email'] ?? 'your@email.com';
$phone = $headerContent['phone'] ?? '';
$location = $headerContent['location'] ?? ($headerContent['city'] ?? '') . ', ' . ($headerContent['country'] ?? '');
?>
<div class="resume-preview" style="font-family: Arial, sans-serif; color: #1e293b; min-height: 400px;">
    <!-- Header with initials circle -->
    <div class="mb-6 pb-4 border-b-2" style="border-color: <?= $primaryColor ?>;">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: <?= $primaryColor ?>; font-size: 18px;">
                <?= $initials ?>
            </div>
            <h1 class="text-xl font-bold" style="color: #111827; font-size: 20px;">
                <?= htmlspecialchars(strtoupper($fullName)) ?>
            </h1>
        </div>
        <div class="text-sm space-y-1" style="color: #4b5563; font-size: 13px;">
            <?php if (!empty($email)): ?>
                <div><?= htmlspecialchars($email) ?></div>
            <?php endif; ?>
            <?php if (!empty($phone)): ?>
                <div><?= htmlspecialchars($phone) ?></div>
            <?php endif; ?>
            <?php if (!empty($location)): ?>
                <div><?= htmlspecialchars($location) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary -->
    <?php 
    $summarySection = $sectionsData['summary'] ?? null;
    if ($summarySection && !empty($summarySection['section_data']['content']['text'])):
    ?>
    <div class="mb-5">
        <h2 class="text-base font-bold mb-2 uppercase" style="color: <?= $primaryColor ?>; font-size: 14px; letter-spacing: 0.5px;">Summary</h2>
        <p class="text-sm leading-relaxed" style="color: #4b5563; font-size: 12px; line-height: 1.6;">
            <?= htmlspecialchars($summarySection['section_data']['content']['text']) ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Skills -->
    <?php 
    $skillsSection = $sectionsData['skills'] ?? null;
    if ($skillsSection && !empty($skillsSection['section_data']['content']['items'])):
    ?>
    <div class="mb-5">
        <h2 class="text-base font-bold mb-3 uppercase" style="color: <?= $primaryColor ?>; font-size: 14px; letter-spacing: 0.5px;">Skills</h2>
        <div class="grid grid-cols-2 gap-2">
            <?php foreach ($skillsSection['section_data']['content']['items'] as $skill): ?>
                <div class="text-sm" style="color: #4b5563; font-size: 12px;">
                    • <?= htmlspecialchars($skill['name'] ?? '') ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Experience -->
    <?php 
    $expSection = $sectionsData['experience'] ?? null;
    if ($expSection && !empty($expSection['section_data']['content']['items'])):
    ?>
    <div class="mb-5">
        <h2 class="text-base font-bold mb-3 uppercase" style="color: <?= $primaryColor ?>; font-size: 14px; letter-spacing: 0.5px;">Experience</h2>
        <?php foreach ($expSection['section_data']['content']['items'] as $exp): ?>
        <div class="mb-4">
            <div class="font-semibold text-sm mb-1" style="font-size: 13px; color: #111827;">
                <?= htmlspecialchars($exp['job_title'] ?? '') ?>
            </div>
            <div class="text-xs mb-2" style="color: #6b7280; font-size: 11px;">
                <?= htmlspecialchars($exp['company_name'] ?? '') ?>
                <?php if (!empty($exp['location'])): ?>
                    | <?= htmlspecialchars($exp['location']) ?>
                <?php endif; ?>
                <?php if (!empty($exp['start_date'])): ?>
                    | <?= date('m/Y', strtotime($exp['start_date'])) ?>
                    <?php if ($exp['is_current'] ?? false): ?>
                        - Current
                    <?php elseif (!empty($exp['end_date'])): ?>
                        - <?= date('m/Y', strtotime($exp['end_date'])) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if (!empty($exp['description'])): ?>
                <p class="text-xs leading-relaxed" style="color: #4b5563; font-size: 11px; line-height: 1.5;">
                    <?= htmlspecialchars($exp['description']) ?>
                </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Education -->
    <?php 
    $eduSection = $sectionsData['education'] ?? null;
    if ($eduSection && !empty($eduSection['section_data']['content']['items'])):
    ?>
    <div class="mb-4">
        <h2 class="text-base font-bold mb-3 uppercase" style="color: <?= $primaryColor ?>; font-size: 14px; letter-spacing: 0.5px;">Education and Training</h2>
        <?php foreach ($eduSection['section_data']['content']['items'] as $edu): ?>
        <div class="mb-3">
            <div class="font-semibold text-sm" style="font-size: 13px; color: #111827;">
                <?= htmlspecialchars($edu['degree'] ?? '') ?>
                <?php if (!empty($edu['field_of_study'])): ?>
                    - <?= htmlspecialchars($edu['field_of_study']) ?>
                <?php endif; ?>
            </div>
            <div class="text-xs" style="color: #6b7280; font-size: 11px;">
                <?= htmlspecialchars($edu['institution'] ?? '') ?>
                <?php if (!empty($edu['start_date'])): ?>
                    | <?= date('Y', strtotime($edu['start_date'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Projects -->
    <?php 
    $additionalSection = $sectionsData['additional'] ?? null;
    if ($additionalSection && !empty($additionalSection['section_data']['content']['projects'])):
    ?>
    <div class="mb-5">
        <h2 class="text-base font-bold mb-3 uppercase" style="color: <?= $primaryColor ?>; font-size: 14px; letter-spacing: 0.5px;">Projects</h2>
        <?php foreach ($additionalSection['section_data']['content']['projects'] as $project): ?>
        <div class="mb-4">
            <div class="font-semibold text-sm mb-1" style="font-size: 13px; color: #111827;">
                <?= htmlspecialchars($project['title'] ?? '') ?>
                <?php if (!empty($project['role'])): ?>
                    - <span class="font-normal" style="color: #4b5563;"><?= htmlspecialchars($project['role']) ?></span>
                <?php endif; ?>
            </div>
            <div class="text-xs mb-2" style="color: #6b7280; font-size: 11px;">
                <?php if (!empty($project['url'])): ?>
                    <a href="<?= htmlspecialchars($project['url']) ?>" target="_blank" style="color: <?= $primaryColor ?>; text-decoration: none;"><?= htmlspecialchars($project['url']) ?></a>
                <?php endif; ?>
                <?php if (!empty($project['start_date'])): ?>
                    <?php if (!empty($project['url'])): ?> | <?php endif; ?>
                    <?= date('m/Y', strtotime($project['start_date'])) ?>
                    <?php if (!empty($project['end_date'])): ?>
                        - <?= date('m/Y', strtotime($project['end_date'])) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if (!empty($project['description'])): ?>
                <p class="text-xs leading-relaxed" style="color: #4b5563; font-size: 11px; line-height: 1.5;">
                    <?= htmlspecialchars($project['description']) ?>
                </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Certifications -->
    <?php 
    if ($additionalSection && !empty($additionalSection['section_data']['content']['certifications'])):
    ?>
    <div class="mb-5">
        <h2 class="text-base font-bold mb-3 uppercase" style="color: <?= $primaryColor ?>; font-size: 14px; letter-spacing: 0.5px;">Certifications</h2>
        <?php foreach ($additionalSection['section_data']['content']['certifications'] as $cert): ?>
        <div class="mb-3">
            <div class="font-semibold text-sm" style="font-size: 13px; color: #111827;">
                <?= htmlspecialchars($cert['name'] ?? '') ?>
            </div>
            <div class="text-xs" style="color: #6b7280; font-size: 11px;">
                <?= htmlspecialchars($cert['issuer'] ?? '') ?>
                <?php if (!empty($cert['date'])): ?>
                    | <?= date('Y', strtotime($cert['date'])) ?>
                <?php endif; ?>
                <?php if (!empty($cert['url'])): ?>
                    | <a href="<?= htmlspecialchars($cert['url']) ?>" target="_blank" style="color: <?= $primaryColor ?>; text-decoration: none;">View Credential</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
