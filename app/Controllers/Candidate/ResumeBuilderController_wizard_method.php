/**
     * Resume builder wizard (step-by-step)
     * GET /candidate/resume/builder/{resumeId}/wizard
     */
    public function wizard(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->redirect('/candidate/resume/builder');
            return;
        }

        $template = $resume->template();
        $sections = $resume->getSectionsArray();
        
        // Convert sections array to associative array by section_type
        $sectionsByType = [];
        foreach ($sections as $section) {
            $sectionType = $section['section_type'] ?? '';
            if ($sectionType) {
                $sectionsByType[$sectionType] = [
                    'id' => $section['id'] ?? null,
                    'section_type' => $sectionType,
                    'section_data' => $section['section_data'] ?? ['content' => []],
                    'sort_order' => $section['sort_order'] ?? 0,
                    'is_visible' => $section['is_visible'] ?? true
                ];
            }
        }

        $response->view('candidate/resume/builder/wizard', [
            'title' => 'Resume Builder - ' . ($resume->attributes['title'] ?? 'My Resume'),
            'candidate' => $candidate,
            'resume' => $resume,
            'template' => $template,
            'sections' => $sections,
            'sectionsData' => $sectionsByType,
            'isPremium' => (bool)($candidate->attributes['is_premium'] ?? false)
        ], 200);
    }
