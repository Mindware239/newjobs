<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Complete Your Profile - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
            transform: translateY(-2px);
        }
        .text-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .step-enter {
            animation: slideIn 0.3s ease-out forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .segmented-bar{height:14px;border-radius:9999px;background:#eef2ff;position:relative;overflow:hidden;box-shadow:inset 0 1px 2px rgba(0,0,0,0.06)}
        .segmented-bar .fill{height:100%;border-radius:9999px;background:linear-gradient(90deg,#22c55e 0%,#84cc16 20%,#f59e0b 50%,#f97316 70%,#8b5cf6 100%);transition:width 400ms ease}
        .segmented-bar .ticks{position:absolute;inset:0;pointer-events:none}
        .segmented-bar .ticks span{position:absolute;top:50%;width:1px;height:10px;background:rgba(0,0,0,0.1);transform:translateY(-50%)}
        .segmented-bar .knob{position:absolute;top:50%;width:16px;height:16px;border-radius:9999px;transform:translateY(-50%);box-shadow:0 2px 6px rgba(0,0,0,0.2);border:2px solid #fff}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 6px 14px rgba(17,24,39,0.06)}
        .card-title{font-size:1rem;font-weight:600;color:#0f172a}
        .donut{--p:0;--c:0;--i:0;--m:0;--th:24px;width:160px;height:160px;border-radius:50%;
              background:
              radial-gradient(farthest-side,#fff calc(50% - var(--th)),transparent calc(50% - var(--th) + 1px)) top/100% 100%,
              conic-gradient(#22c55e calc(var(--c) * 1%), #f59e0b 0 calc((var(--c) + var(--i)) * 1%), #f472b6 0);
              background-repeat:no-repeat;position:relative;box-shadow:0 6px 14px rgba(17,24,39,0.06);transition:background 600ms ease}
        .donut::after{content:attr(data-label);position:absolute;inset:0;display:grid;place-items:center;font-weight:700;color:#111827;font-size:22px}
        input[type="text"], input[type="date"], input[type="tel"], input[type="email"], select, textarea { border-width:1.5px; }
        input, select, textarea { transition: box-shadow .2s ease, border-color .2s ease; }
        input:focus, select:focus, textarea:focus { outline: none; box-shadow:0 0 0 3px rgba(99,102,241,0.25); border-color:#6366f1; }
        .sb-bar{width:100%;height:8px;background:#e5e7eb;border-radius:9999px;position:relative;overflow:hidden}
        .sb-fill{height:100%;border-radius:9999px;transition:width 400ms ease}
        .sb-knob{position:absolute;top:50%;transform:translate(-50%,-50%);width:10px;height:10px;border-radius:9999px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.2)}
        .toast{position:fixed;right:20px;bottom:20px;z-index:9999;display:flex;flex-direction:column;gap:8px}
        .toast .item{min-width:260px;max-width:360px;padding:10px 12px;border-radius:10px;color:#fff;box-shadow:0 6px 14px rgba(17,24,39,0.15);display:flex;align-items:center;gap:8px}
        .toast .success{background:#16a34a}
        .toast .error{background:#dc2626}
        .toast .info{background:#2563eb}
        .chart-tooltip{position:absolute;background:#1f2937;color:#fff;border-radius:8px;padding:8px 10px;font-size:12px;box-shadow:0 8px 20px rgba(0,0,0,0.25);white-space:nowrap;pointer-events:none;z-index:10}
        input, select, textarea { padding: 10px 12px; line-height: 1.4; }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('profileForm', () => ({
                currentStep: 1,
                profileStrength: <?= $candidate->attributes['profile_strength'] ?? 0 ?>,
                allSkills: <?= json_encode($allSkills ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                newLanguage: { language: '', proficiency: 'conversational' },
                isPremium: <?= $candidate->isPremium() ? 'true' : 'false' ?>,
                recordingSupported: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                isRecording: false,
                mediaStream: null,
                mediaRecorder: null,
                recordedChunks: [],
                recordedBlob: null,
                toasts: [],
                lastCreatedEmploymentId: null,
                tipVisible: false,
                tipText: '',
                tipX: 0,
                tipY: 0,
                saving: false,
                init() {
                    this.$nextTick(() => {
                        this.drawPie();
                        if (this.$watch) {
                            this.$watch('overallPercent', () => this.drawPie());
                            this.$watch('completionBreakdown', () => this.drawPie());
                        }
                        const emps = this.formData?.verification?.employments || [];
                        emps.forEach((_, idx) => this.refreshEmploymentStatus(idx));
                    });
                },
                formData: {
                    basic: {
                        full_name: <?php 
                            $userName = $candidate->attributes['full_name'] ?? '';
                            if (empty($userName) && isset($user)) {
                                if (is_array($user)) {
                                    $userName = $user['google_name'] ?? $user['apple_name'] ?? $user['full_name'] ?? '';
                                } elseif (is_object($user) && isset($user->attributes)) {
                                    $userName = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? $user->attributes['full_name'] ?? '';
                                }
                            }
                            echo json_encode($userName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>,
                        dob: <?= json_encode($candidate->attributes['dob'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        professional_title: <?= json_encode($candidate->attributes['professional_title'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        gender: <?= json_encode($candidate->attributes['gender'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        mobile: <?php 
                            $userPhone = $candidate->attributes['mobile'] ?? '';
                            if (empty($userPhone) && isset($user)) {
                                if (is_array($user)) {
                                    $userPhone = $user['phone'] ?? '';
                                } elseif (is_object($user) && isset($user->attributes)) {
                                    $userPhone = $user->attributes['phone'] ?? '';
                                }
                            }
                            echo json_encode($userPhone, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>,
                        city: <?= json_encode($candidate->attributes['city'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        state: <?= json_encode($candidate->attributes['state'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        country: <?= json_encode($candidate->attributes['country'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        profile_picture: <?= json_encode($candidate->attributes['profile_picture'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        resume_url: <?= json_encode($candidate->attributes['resume_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        video_intro_url: <?= json_encode($candidate->attributes['video_intro_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        video_type: <?= json_encode($candidate->attributes['video_intro_type'] ?? 'upload', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        video_url: <?= json_encode($candidate->attributes['video_intro_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        self_introduction: <?= json_encode($candidate->attributes['self_introduction'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                    },
                    education: <?= json_encode($existingEducation ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    experience: <?= json_encode($existingExperience ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    skills: <?= json_encode($existingSkills ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    languages: <?= json_encode($existingLanguages ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    certificates: <?= json_encode($existingCertificates ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    verification: <?= json_encode($existingVerification ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    additional: {
                        expected_salary_min: <?= json_encode($candidate->attributes['expected_salary_min'] ?? null) ?>,
                        expected_salary_max: <?= json_encode($candidate->attributes['expected_salary_max'] ?? null) ?>,
                        current_salary: <?= json_encode($candidate->attributes['current_salary'] ?? null) ?>,
                        notice_period: <?= json_encode($candidate->attributes['notice_period'] ?? null) ?>,
                        preferred_job_location: <?= json_encode($candidate->attributes['preferred_job_location'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        portfolio_url: <?= json_encode($candidate->attributes['portfolio_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        linkedin_url: <?= json_encode($candidate->attributes['linkedin_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        github_url: <?= json_encode($candidate->attributes['github_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        website_url: <?= json_encode($candidate->attributes['website_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                    }
                },
                addEducation() {
                    this.formData.education.push({ degree: '', field_of_study: '', institution: '', end_date: '', grade: '' });
                },
                removeEducation(index) {
                    this.formData.education.splice(index, 1);
                },
                addExperience() {
                    this.formData.experience.push({ job_title: '', company_name: '', start_date: '', end_date: '', description: '', is_current: false });
                },
                removeExperience(index) {
                    this.formData.experience.splice(index, 1);
                },
                addSkill() {
                    this.formData.skills.push({ name: '', level: 'beginner' });
                },
                removeSkill(index) {
                    this.formData.skills.splice(index, 1);
                },
                addLanguage() {
                    this.formData.languages.push({ language: '', proficiency: 'conversational' });
                },
                removeLanguage(index) {
                    this.formData.languages.splice(index, 1);
                },
                addCertificate() {
                    this.formData.certificates.push({ name: '', issuing_organization: '', issue_date: '', credential_id: '', credential_url: '' });
                },
                removeCertificate(index) {
                    this.formData.certificates.splice(index, 1);
                },
                addEmployment() {
                     if (!this.formData.verification.employments) this.formData.verification.employments = [];
                     this.formData.verification.employments.push({
                         company: '',
                         role: '',
                         type: 'Full-time',
                         start_date: '',
                         end_date: '',
                         is_current: false,
                         documents: { offer_letter: null, relieving_letter: null, salary_slip: null },
                         documentNames: {},
                         uploadingDocs: {},
                         requesting: false,
                         employment_id: null,
                         status_overall: 'under_review',
                         consent: false,
                         hr_email: '',
                         hr_phone: '',
                         manager_email: '',
                         company_website: '',
                         cin: '',
                         gst: '',
                         docsUploaded: false,
                         emailSent: false,
                         hrResponded: false
                     });
                },
                removeEmployment(index) {
                    this.formData.verification.employments.splice(index, 1);
                },
                showToast(msg, type = 'info') {
                    this.toasts = this.toasts || [];
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, msg, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 3500);
                },
                drawPie() {
                    const layer = this.$refs?.pieLayer;
                    if (!layer) return;
                    while (layer.firstChild) layer.removeChild(layer.firstChild);
                    const paths = this.piePaths();
                    const ns = 'http://www.w3.org/2000/svg';
                    const self = this;
                    const total = Math.max((self.completionBreakdown.complete||0) + (self.completionBreakdown.inProgress||0) + (self.completionBreakdown.missing||0), 1);
                    paths.forEach(p => {
                        const path = document.createElementNS(ns, 'path');
                        path.setAttribute('d', p.d);
                        path.setAttribute('fill', p.color);
                        const title = document.createElementNS(ns, 'title');
                        const pct = Math.round((p.value / total) * 100);
                        title.textContent = `${p.label} (${p.value}, ${pct}%)`;
                        path.appendChild(title);
                        path.addEventListener('mousemove', function(e){
                            const svg = e.currentTarget.ownerSVGElement;
                            const rect = svg.getBoundingClientRect();
                            self.tipText = `${p.label} (${p.value}, ${pct}%)`;
                            self.tipX = e.clientX - rect.left + 12;
                            self.tipY = e.clientY - rect.top - 12;
                            self.tipVisible = true;
                        });
                        path.addEventListener('mouseleave', function(){
                            self.tipVisible = false;
                        });
                        layer.appendChild(path);
                    });
                },
                async saveEmploymentBlock(index) {
                    const emp = this.formData.verification.employments[index];
                    if (!emp) return;
                    // Only create once
                    if (!emp.employment_id) {
                        if (emp._creating) return null;
                        emp._creating = true;
                        const fd = new FormData();
                        fd.append('company_name', emp.company || '');
                        fd.append('designation', emp.role || '');
                        fd.append('employee_id', '');
                        fd.append('start_date', emp.start_date || '');
                        fd.append('end_date', emp.is_current ? '' : (emp.end_date || ''));
                        fd.append('consent', emp.consent ? '1' : '0');
                        try {
                            const res = await fetch('/candidate/verification', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-Token': document.querySelector('meta[name=\"csrf-token\"]')?.content || '',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: fd
                            });
                            let data = null;
                            try { data = await res.json(); } catch (_) {}
                            if (data && data.success) {
                                this.lastCreatedEmploymentId = data.employment_id;
                                emp.employment_id = data.employment_id;
                                try { await this.saveSection('verification'); } catch (_) {}
                                this.refreshEmploymentStatus(index);
                                this.showToast('Employment record created', 'success');
                                emp._creating = false;
                                return emp.employment_id;
                            } else {
                                this.showToast((data && data.error) ? ('Error: ' + data.error) : 'Failed to create employment record', 'error');
                                emp._creating = false;
                                return null;
                            }
                        } catch (e) { this.showToast('Network error creating employment', 'error'); emp._creating = false; return null; }
                    }
                    return emp.employment_id;
                },
                async uploadEmploymentDocument(index, docType, file) {
                    const emp = this.formData.verification.employments[index];
                    if (!emp) return;
                    const createdId = await this.saveEmploymentBlock(index);
                    if (!emp.employment_id && createdId) emp.employment_id = createdId;
                    if (!emp.employment_id) { this.showToast('Please fill company details first', 'error'); return; }
                    // show selected filename immediately
                    emp.documentNames = { ...(emp.documentNames || {}), [docType]: (file?.name || 'Selected file') };
                    if (!emp.employment_id || !file) return;
                    emp.uploadError = null;
                    emp.documents = emp.documents || {};
                    emp.uploadingDocs = { ...(emp.uploadingDocs || {}), [docType]: true };
                    const fd = new FormData();
                    fd.append('doc_type', docType);
                    fd.append('file', file);
                    try {
                        const res = await fetch(`/candidate/verification/${emp.employment_id}/documents`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': document.querySelector('meta[name=\"csrf-token\"]')?.content || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: fd
                        });
                        let data = null;
                        try { data = await res.json(); } catch (_) {}
                        if (data && data.success) {
                            const fileUrl = (data.document && (data.document.file_url || data.document.file_path)) ? (data.document.file_url || data.document.file_path) : 'uploaded';
                            const displayName = (data?.document?.file_name) ? data.document.file_name : (file.name || 'Uploaded');
                            emp.documents = { ...emp.documents, [docType]: fileUrl };
                            emp.documentNames = { ...(emp.documentNames || {}), [docType]: displayName };
                            emp.docsUploaded = true;
                            try { await this.saveSection('verification'); } catch (_) {}
                            this.refreshEmploymentStatus(index);
                            this.showToast('Document uploaded: ' + (emp.documentNames[docType] || docType), 'success');
                        } else {
                            const needRecreate = (res && (res.status === 403 || res.status === 404)) || (data && (data.error === 'unauthorized' || data.error === 'not_found'));
                            if (needRecreate) {
                                emp.employment_id = null;
                                const newId = await this.saveEmploymentBlock(index);
                                if (newId) {
                                    emp.employment_id = newId;
                                    const fd2 = new FormData();
                                    fd2.append('doc_type', docType);
                                    fd2.append('file', file);
                                    const res2 = await fetch(`/candidate/verification/${emp.employment_id}/documents`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        credentials: 'same-origin',
                                        body: fd2
                                    });
                                    let data2 = null;
                                    try { data2 = await res2.json(); } catch (_) {}
                                    if (data2 && data2.success) {
                                        const fileUrl2 = (data2.document && (data2.document.file_url || data2.document.file_path)) ? (data2.document.file_url || data2.document.file_path) : 'uploaded';
                                        const displayName2 = (data2?.document?.file_name) ? data2.document.file_name : (file.name || 'Uploaded');
                                        emp.documents = { ...emp.documents, [docType]: fileUrl2 };
                                        emp.documentNames = { ...(emp.documentNames || {}), [docType]: displayName2 };
                                        emp.docsUploaded = true;
                                        try { await this.saveSection('verification'); } catch (_) {}
                                        this.refreshEmploymentStatus(index);
                                        this.showToast('Document uploaded: ' + (emp.documentNames[docType] || docType), 'success');
                                        return;
                                    }
                                }
                            }
                            emp.uploadError = (data && (data.error || data.message)) ? (data.error || data.message) : (`Upload failed${res && res.status ? ' ('+res.status+')' : ''}`);
                            this.showToast(emp.uploadError, 'error');
                        }
                    } catch (e) { this.showToast('Network error during upload', 'error'); }
                    finally {
                        emp.uploadingDocs = { ...(emp.uploadingDocs || {}), [docType]: false };
                    }
                },
                replaceEmploymentDocument(index, docType) {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = '.pdf,.doc,.docx,.jpg,.jpeg';
                    input.onchange = (e) => {
                        const file = e.target.files && e.target.files[0];
                        if (file) this.uploadEmploymentDocument(index, docType, file);
                    };
                    input.click();
                },
                async requestVerification(index) {
                    const emp = this.formData.verification.employments[index];
                    if (!emp) return;
                    const createdId = await this.saveEmploymentBlock(index);
                    const eid = emp.employment_id || createdId || this.lastCreatedEmploymentId;
                    if (!eid) { this.showToast('Couldn’t prepare verification. Please try again.', 'error'); return; }
                    emp.employment_id = eid;
                    // Basic validations
                    if (!emp.consent) { this.showToast('Please provide consent to contact employer', 'error'); return; }
                    const email = (emp.hr_email || '').trim();
                    if (!email || !email.includes('@')) { this.showToast('Enter a valid HR email', 'error'); return; }
                    const free = ['gmail.com','yahoo.com','rediffmail.com','outlook.com','hotmail.com','proton.me','icloud.com','zoho.com'];
                    const domain = email.substring(email.lastIndexOf('@') + 1).toLowerCase();
                    if (free.includes(domain)) { this.showToast('Use official company domain email', 'error'); return; }
                    const fd = new FormData();
                    fd.append('hr_email', emp.hr_email || '');
                    fd.append('hr_phone', emp.hr_phone || '');
                    fd.append('manager_email', emp.manager_email || '');
                    fd.append('company_website', emp.company_website || '');
                    fd.append('cin', emp.cin || '');
                    fd.append('gst', emp.gst || '');
                    fd.append('consent', emp.consent ? '1' : '0');
                    emp.requesting = true;
                    this.showToast('Sending verification request…', 'info');
                    try {
                        const res = await fetch(`/candidate/verification/${emp.employment_id}/hr`, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-Token': document.querySelector('meta[name=\"csrf-token\"]')?.content || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: fd
                        });
                        let data = null;
                        const ct = res.headers.get('content-type') || '';
                        if (ct.includes('application/json')) {
                            data = await res.json();
                        } else {
                            const text = await res.text();
                            data = {};
                            if (res.status === 403 && text.toLowerCase().includes('csrf')) {
                                data.error = 'csrf';
                            } else if (!res.ok) {
                                data.error = 'server_error';
                            } else {
                                data.success = true;
                            }
                        }
                        if (data && data.success) {
                            emp.emailSent = true;
                            emp.status_overall = 'under_review';
                            this.refreshEmploymentStatus(index);
                            this.showToast('Verification request sent to HR', 'success');
                        } else if (data && data.error === 'invalid_email_domain') {
                            this.showToast('Please use an official company domain email for HR.', 'error');
                        } else if (data && data.error === 'already_sent') {
                            this.showToast('A verification request is already pending. Please wait.', 'error');
                        } else if (data && data.error === 'already_verified') {
                            this.showToast('This employment is already verified. No repeat requests.', 'info');
                        } else if (data && data.error === 'send_failed') {
                            this.showToast('Email sending failed. Please try again later.', 'error');
                        } else if (data && data.error === 'csrf') {
                            this.showToast('Session expired. Please refresh the page and try again.', 'error');
                        } else {
                            this.showToast((data && data.error) ? ('Error: ' + data.error) : 'Failed to send verification request', 'error');
                        }
                    } catch (e) { this.showToast('Network error while sending verification request', 'error'); }
                    finally { emp.requesting = false; }
                },
                async refreshEmploymentStatus(index) {
                    const emp = this.formData.verification.employments[index];
                    if (!emp || !emp.employment_id) return;
                    try {
                        const res = await fetch(`/api/candidate/verification/${emp.employment_id}/status`, { method: 'GET' });
                        const data = await res.json();
                        if (data && data.record) {
                            emp.status_overall = data.record.status_overall || 'under_review';
                            emp.docsUploaded = (data.documents_count || 0) > 0;
                            emp.emailSent = !!data.request;
                            emp.hrResponded = !!data.response;
                            emp.requesting = false;
                            emp.request = data.request || null;
                            emp.response = data.response || null;
                            const docs = data.documents || [];
                            emp.documents = emp.documents || {};
                            emp.documentNames = emp.documentNames || {};
                            docs.forEach(d => {
                                const t = d.doc_type;
                                emp.documents[t] = d.url;
                                emp.documentNames[t] = d.file_name;
                            });
                        }
                    } catch (e) {}
                    finally { this.drawPie(); }
                },
                countdownText(expiresAt) {
                    if (!expiresAt) return '';
                    const end = new Date(expiresAt);
                    const now = new Date();
                    const diffMs = end - now;
                    if (diffMs <= 0) return 'Expired';
                    const totalMinutes = Math.floor(diffMs / 60000);
                    const days = Math.floor(totalMinutes / (60 * 24));
                    const hours = Math.floor((totalMinutes % (60 * 24)) / 60);
                    const mins = totalMinutes % 60;
                    const parts = [];
                    if (days > 0) parts.push(days + 'd');
                    if (hours > 0) parts.push(hours + 'h');
                    parts.push(mins + 'm');
                    return parts.join(' ');
                },
                polar(cx, cy, r, a) {
                    const rad = (a - 90) * Math.PI / 180;
                    return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) };
                },
                arcPath(cx, cy, R, r, a0, a1) {
                    const p1 = this.polar(cx, cy, R, a0);
                    const p2 = this.polar(cx, cy, R, a1);
                    const ip2 = this.polar(cx, cy, r, a1);
                    const ip1 = this.polar(cx, cy, r, a0);
                    const large = (a1 - a0) > 180 ? 1 : 0;
                    return `M ${p1.x},${p1.y} A ${R},${R},0, ${large},1, ${p2.x},${p2.y} L ${ip2.x},${ip2.y} A ${r},${r},0, ${large},0, ${ip1.x},${ip1.y} Z`;
                },
                piePaths() {
                    const c = this.completionBreakdown.complete || 0;
                    const i = this.completionBreakdown.inProgress || 0;
                    const m = this.completionBreakdown.missing || 0;
                    const total = Math.max(c + i + m, 1);
                    const segs = [
                        { label: 'Completed', color: '#10B981', value: c },
                        { label: 'In Progress', color: '#F59E0B', value: i },
                        { label: 'Missing', color: '#EF4444', value: m }
                    ];
                    let angle = 0;
                    const paths = [];
                    segs.forEach(s => {
                        const span = (s.value / total) * 360;
                        const a0 = angle;
                        const a1 = angle + span;
                        angle = a1;
                        if (span <= 0) return;
                        const d = this.arcPath(145, 100, 85, 60, a0, a1);
                        paths.push({ d, color: s.color, label: s.label, value: s.value });
                    });
                    return paths;
                },
                get totalVerifiedExperience() {
                    if (!this.formData.verification.employments) return '0 Years';
                    let totalMonths = 0;
                    this.formData.verification.employments.forEach(emp => {
                        if (emp.start_date) {
                            let start = new Date(emp.start_date);
                            let end = emp.is_current ? new Date() : (emp.end_date ? new Date(emp.end_date) : new Date());
                            let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
                            if (months > 0) totalMonths += months;
                        }
                    });
                    let years = Math.floor(totalMonths / 12);
                    let remainingMonths = totalMonths % 12;
                    if (years === 0 && remainingMonths === 0) return '0 Years';
                    let result = [];
                    if (years > 0) result.push(years + (years === 1 ? ' Year' : ' Years'));
                    if (remainingMonths > 0) result.push(remainingMonths + (remainingMonths === 1 ? ' Month' : ' Months'));
                    return result.join(' ');
                },
                async saveSection(section) {
                    let payload = { section };
                    if (section === 'basic') {
                        const b = this.formData.basic;
                        const videoUrl = b.video_type === 'youtube' ? (b.video_url || '') : (b.video_intro_url || '');
                        payload = {
                            section: 'basic',
                            full_name: b.full_name || '',
                            professional_title: b.professional_title || '',
                            dob: b.dob || '',
                            gender: b.gender || '',
                            mobile: b.mobile || '',
                            city: b.city || '',
                            state: b.state || '',
                            country: b.country || '',
                            self_introduction: b.self_introduction || '',
                            profile_picture: b.profile_picture || '',
                            resume_url: b.resume_url || '',
                            video_intro_type: b.video_type || '',
                            video_intro_url: videoUrl || ''
                        };
                    } else if (section === 'education') {
                        payload = {
                            section: 'education',
                            education: (this.formData.education || []).map(e => ({
                                degree: e.degree || '',
                                field_of_study: e.field_of_study || '',
                                institution: e.institution || '',
                                start_date: e.start_date || null,
                                end_date: e.end_date || null,
                                is_current: e.is_current ? 1 : 0,
                                grade: e.grade || null,
                                description: e.description || null
                            }))
                        };
                    } else if (section === 'experience') {
                        payload = {
                            section: 'experience',
                            experience: (this.formData.experience || []).map(x => ({
                                job_title: x.job_title || '',
                                company_name: x.company_name || x.company || '',
                                start_date: x.start_date || null,
                                end_date: x.end_date || null,
                                is_current: x.is_current ? 1 : 0,
                                description: x.description || null,
                                location: x.location || null
                            }))
                        };
                    } else if (section === 'skills') {
                        payload = {
                            section: 'skills',
                            skills: (this.formData.skills || []).map(s => ({
                                name: s.name || '',
                                proficiency_level: s.level || 'beginner',
                                years_of_experience: s.years_of_experience || null
                            }))
                        };
                    } else if (section === 'languages') {
                        payload = {
                            section: 'languages',
                            languages: (this.formData.languages || []).map(l => ({
                                language: l.language || '',
                                proficiency: l.proficiency || 'conversational'
                            }))
                        };
                    } else if (section === 'certificates') {
                        payload = {
                            section: 'certificates',
                            certificates: (this.formData.certificates || []).map(c => ({
                                name: c.name || '',
                                issuing_organization: c.issuing_organization || '',
                                issue_date: c.issue_date || null,
                                credential_id: c.credential_id || null,
                                credential_url: c.credential_url || null
                            }))
                        };
                    } else if (section === 'verification') {
                        const v = this.formData.verification;
                        payload = {
                            section: 'verification',
                            need_verification: v.need_verification || false,
                            employments: v.employments || []
                        };
                    } else if (section === 'additional') {
                        const a = this.formData.additional;
                        payload = {
                            section: 'additional',
                            expected_salary_min: a.expected_salary_min ?? null,
                            expected_salary_max: a.expected_salary_max ?? null,
                            current_salary: a.current_salary ?? null,
                            notice_period: a.notice_period ?? null,
                            preferred_job_location: a.preferred_job_location || '',
                            portfolio_url: a.portfolio_url || '',
                            linkedin_url: a.linkedin_url || '',
                            github_url: a.github_url || '',
                            website_url: a.website_url || ''
                        };
                    }
                    this.saving = true;
                    try {
                        const res = await fetch('/candidate/profile/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        const result = await res.json();
                        if (result && result.success) {
                            this.profileStrength = result.profile_strength ?? this.profileStrength;
                            if (section === 'additional') {
                                this.showToast('Profile completed successfully! Redirecting...', 'success');
                                setTimeout(() => {
                                    window.location.href = '/candidate/profile';
                                }, 1500);
                            } else {
                                this.showToast('Section saved successfully', 'success');
                            }
                        } else {
                            this.showToast(result.message || 'Error saving section', 'error');
                        }
                    } catch (e) {
                        this.showToast('Network error saving section', 'error');
                    } finally {
                        this.saving = false;
                    }
                },
                async uploadFile(type, file) {
                    const fd = new FormData();
                    fd.append('type', type);
                    fd.append('file', file);
                    const res = await fetch('/candidate/profile/upload', {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: fd
                    });
                    let out = null;
                    try {
                        out = await res.json();
                        if (!res.ok) {
                             this.showToast(out.error || 'Upload failed', 'error');
                             return {};
                        }
                    } catch (_) {
                        const txt = await res.text();
                        this.showToast((txt && txt.length < 300 ? txt : 'Upload failed'), 'error');
                        return {};
                    }
                    if (out && out.url) {
                        this.showToast('File uploaded successfully', 'success');
                        if (type === 'profile_picture') {
                            this.formData.basic.profile_picture = out.url;
                        } else if (type === 'resume') {
                            this.formData.basic.resume_url = out.url;
                        } else if (type === 'video') {
                            this.formData.basic.video_intro_url = out.url;
                            this.formData.basic.video_type = 'upload';
                        }
                    }
                    return out;
                },
                async startRecording() {
                    if (!this.isPremium) { return; }
                    if (!this.recordingSupported || this.isRecording) { return; }
                    try {
                        this.recordedChunks = [];
                        this.recordedBlob = null;
                        this.mediaStream = await navigator.mediaDevices.getUserMedia({
                            video: { width: 640, height: 360, frameRate: 24 },
                            audio: true
                        });
                        this.$refs.recPreview.srcObject = this.mediaStream;
                        let mime = 'video/webm;codecs=vp9';
                        if (!MediaRecorder.isTypeSupported(mime)) {
                            mime = 'video/webm;codecs=vp8';
                        }
                        if (!MediaRecorder.isTypeSupported(mime)) {
                            mime = 'video/webm';
                        }
                        this.mediaRecorder = new MediaRecorder(this.mediaStream, { mimeType: mime, bitsPerSecond: 800000 });
                        this.mediaRecorder.ondataavailable = e => {
                            if (e.data && e.data.size > 0) this.recordedChunks.push(e.data);
                        };
                        this.mediaRecorder.onstop = () => {
                            this.recordedBlob = new Blob(this.recordedChunks, { type: mime });
                        };
                        this.mediaRecorder.start();
                        this.isRecording = true;
                    } catch (err) {}
                },
                async stopRecording() {
                    if (!this.isRecording || !this.mediaRecorder) { return; }
                    this.mediaRecorder.stop();
                    this.isRecording = false;
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(t => t.stop());
                        this.mediaStream = null;
                    }
                },
                async saveRecording() {
                    if (!this.recordedBlob) { return; }
                    const file = new File([this.recordedBlob], 'intro.webm', { type: this.recordedBlob.type || 'video/webm' });
                    await this.uploadFile('video', file);
                },
                get sectionPercentages() {
                    const pct = (filled, total) => Math.min(100, Math.round((filled / Math.max(total,1)) * 100));
                    const b = this.formData.basic || {};
                    const basicFilled = ['full_name','dob','gender','mobile','city','state','country'].filter(k => (b[k] || '').toString().trim()).length;
                    const basicPct = pct(basicFilled, 7);
                    const eduPct = (this.formData.education || []).length > 0 ? 100 : 0;
                    const expPct = (this.formData.experience || []).length > 0 ? 100 : 0;
                    const skillsPct = (this.formData.skills || []).length > 0 ? 100 : 0;
                    const langPct = (this.formData.languages || []).length > 0 ? 100 : 0;
                    const certPct = (this.formData.certificates || []).length > 0 ? 100 : 0;
                    const v = this.formData.verification || {};
                    let verDocs = 0;
                    (v.employments || []).forEach(emp => {
                        const d = emp.documents || {};
                        verDocs += ['offer_letter','relieving_letter','salary_slip'].filter(t => d[t]).length > 0 ? 1 : 0;
                    });
                    const verPct = v.need_verification ? pct(verDocs, Math.max((v.employments || []).length,1)) : 0;
                    const a = this.formData.additional || {};
                    const addFilled = ['expected_salary_min','expected_salary_max','notice_period','preferred_job_location'].filter(k => (a[k] ?? '').toString().trim()).length;
                    const addPct = pct(addFilled, 4);
                    return {
                        basic: basicPct,
                        education: eduPct,
                        experience: expPct,
                        skills: skillsPct,
                        languages: langPct,
                        certificates: certPct,
                        verification: verPct,
                        additional: addPct
                    };
                },
                get overallPercent() {
                    const s = this.sectionPercentages;
                    const vals = Object.values(s);
                    const avg = Math.round(vals.reduce((a,b)=>a+b,0) / Math.max(vals.length,1));
                    return avg;
                },
                get completionBreakdown() {
                    const s = this.sectionPercentages;
                    let complete = 0, inProgress = 0, missing = 0;
                    Object.values(s).forEach(v => {
                        if (v >= 100) complete++;
                        else if (v > 0) inProgress++;
                        else missing++;
                    });
                    const total = Math.max(complete + inProgress + missing, 1);
                    const completePct = Math.round(complete / total * 100);
                    const inProgressPct = Math.round(inProgress / total * 100);
                    const missingPct = Math.max(0, 100 - completePct - inProgressPct);
                    return { complete, inProgress, missing, completePct, inProgressPct, missingPct };
                },
                sectionColor(key) {
                    const map = {
                        basic: '#22c55e',
                        education: '#6366f1',
                        experience: '#10b981',
                        skills: '#f59e0b',
                        languages: '#fbbf24',
                        certificates: '#f472b6',
                        verification: '#14b8a6',
                        additional: '#a78bfa'
                    };
                    return map[key] || '#6366f1';
                },
                get donutCallout() {
                    const br = this.completionBreakdown;
                    let target = br.missing > 0 
                        ? { pct: br.missingPct, label: 'Missing: ' + br.missing, color: '#f472b6', start: br.completePct + br.inProgressPct }
                        : (br.inProgress > 0 
                            ? { pct: br.inProgressPct, label: 'In Progress: ' + br.inProgress, color: '#f59e0b', start: br.completePct }
                            : { pct: br.completePct, label: 'Completed: ' + br.complete, color: '#22c55e', start: 0 });
                    const mid = target.start + target.pct / 2;
                    const angle = mid * 3.6;
                    const rad = angle * Math.PI / 180;
                    const R = 80; // donut half-size (for 160px)
                    const th = 24;
                    const r = R - th - 10;
                    const x = Math.round(R + r * Math.cos(rad));
                    const y = Math.round(R + r * Math.sin(rad));
                    return {
                        left: x + 'px',
                        top: y + 'px',
                        label: target.label,
                        color: target.color
                    };
                },
                async deleteVideo() {
                    try {
                        const res = await fetch('/candidate/profile/delete-video', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const out = await res.json();
                        if (out && out.success) {
                            this.formData.basic.video_intro_url = '';
                            this.formData.basic.video_url = '';
                            this.formData.basic.video_type = 'upload';
                            if (typeof out.profile_strength !== 'undefined') {
                                this.profileStrength = out.profile_strength;
                            }
                        } else {
                            alert(out.error || 'Failed to delete video');
                        }
                    } catch (e) {
                        alert('Network error while deleting video');
                    }
                },
                nextStep() {
                    if (this.currentStep < 8) this.currentStep++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                prevStep() {
                    if (this.currentStep > 1) this.currentStep--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                async saveProfile() {
                    try {
                        const response = await fetch('/candidate/profile/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.formData)
                        });
                        const result = await response.json();
                        if (result.success) {
                            alert('Profile saved successfully!');
                            window.location.reload();
                        } else {
                            alert('Error saving profile: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while saving profile.');
                    }
                }
            }));
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="profileForm" x-cloak>
        <!-- Shared Header -->
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>
        
        <!-- Profile Strength Indicator -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Profile Strength: <strong class="text-indigo-600" x-text="profileStrength + '%'"></strong></span>
                    <a href="/candidate/dashboard" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Skip for now</a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h1 class="text-3xl font-bold text-gray-900">Complete Your Profile</h1>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Completion</div>
                        <div class="text-2xl font-bold text-indigo-600" x-text="overallPercent + '%'"></div>
                    </div>
                </div>
                <div class="segmented-bar">
                    <div class="fill" :style="'width: ' + overallPercent + '%'"></div>
                    <div class="knob" :style="'left: calc(' + overallPercent + '% - 8px)'" :class="overallPercent>=75 ? 'bg-purple-500' : overallPercent>=50 ? 'bg-orange-500' : 'bg-green-500'"></div>
                    <div class="ticks">
                        <span style="left:0%"></span>
                        <span style="left:25%"></span>
                        <span style="left:50%"></span>
                        <span style="left:75%"></span>
                        <span style="left:100%"></span>
                    </div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                    <span>Start</span>
                    <span>25%</span>
                    <span>50%</span>
                    <span>75%</span>
                    <span>Complete</span>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 p-4 overflow-x-auto">
                        <div class="flex flex-nowrap gap-3 min-w-max">
                            <template x-for="(step, index) in ['Basic Details', 'Education', 'Experience', 'Skills', 'Languages', 'Certificates', 'Employer Verification', 'Additional']">
                                <button @click="currentStep = index + 1" 
                                        :class="currentStep === index + 1 ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 whitespace-nowrap">
                                    <span x-text="(index + 1) + '. ' + step"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <form @submit.prevent="saveProfile" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 md:p-8 min-h-[500px] relative">
                
                <!-- Step 1: Basic Details -->
                <div x-show="currentStep === 1" x-transition:enter="step-enter" class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" x-model="formData.basic.full_name" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Enter your full name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Professional Title / Designation</label>
                            <input type="text" x-model="formData.basic.professional_title" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="e.g. PHP Developer, Marketing Manager">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" x-model="formData.basic.dob" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select x-model="formData.basic.gender" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer Not to Say</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                            <input type="tel" x-model="formData.basic.mobile" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Enter mobile number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" x-model="formData.basic.city" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="City">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <input type="text" x-model="formData.basic.state" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="State">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input type="text" x-model="formData.basic.country" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Country">
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold text-gray-800">Introduction</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Self Introduction</label>
                            <textarea x-model="formData.basic.self_introduction" rows="4" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Tell us about yourself..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <img x-show="formData.basic.profile_picture" :src="formData.basic.profile_picture" class="w-full h-full object-cover" alt="">
                                        <span x-show="!formData.basic.profile_picture" class="text-gray-400 text-xs">No Image</span>
                                    </div>
                                    <label class="px-3 py-2 btn-primary rounded-md text-sm cursor-pointer">
                                        <input type="file" class="hidden" accept="image/*" @change="($event.target.files[0]) && uploadFile('profile_picture', $event.target.files[0])">
                                        Upload
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Resume / CV</label>
                                <div class="flex items-center gap-3">
                                    <input type="file" accept=".pdf,.doc,.docx" @change="($event.target.files[0]) && uploadFile('resume', $event.target.files[0])" class="text-sm">
                                    <span x-show="formData.basic.resume_url" class="text-xs text-indigo-600">Uploaded</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Self-Introduction Video</label>
                            <div class="flex items-center gap-6">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="formData.basic.video_type" value="upload">
                                    <span>Upload</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="formData.basic.video_type" value="youtube">
                                    <span>YouTube Link</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm" :class="!isPremium ? 'opacity-50 cursor-not-allowed' : ''">
                                    <input type="radio" x-model="formData.basic.video_type" value="record" :disabled="!isPremium">
                                    <span>Record</span>
                                </label>
                            </div>
                            <div x-show="formData.basic.video_type === 'upload'">
                                <input type="file" accept="video/mp4,video/*" @change="($event.target.files[0]) && uploadFile('video', $event.target.files[0])" class="text-sm">
                            </div>
                            <div x-show="formData.basic.video_type === 'youtube'">
                                <input type="url" x-model="formData.basic.video_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div x-show="formData.basic.video_type === 'record'">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="mb-3 text-sm" x-show="!isPremium">Premium required to record video.</div>
                                    <div x-show="isPremium">
                                        <div class="aspect-video bg-black rounded-lg overflow-hidden">
                                            <video x-ref="recPreview" autoplay playsinline muted class="w-full h-full"></video>
                                        </div>
                                        <div class="mt-3 flex items-center gap-3">
                                            <button type="button" class="px-4 py-2 btn-primary rounded-md text-sm" @click="startRecording()" :disabled="isRecording || !recordingSupported">Start Recording</button>
                                            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm" @click="stopRecording()" :disabled="!isRecording">Stop</button>
                                            <button type="button" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-md text-sm" @click="saveRecording()" :disabled="!recordedBlob">Save Video</button>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-2">640×360, 24fps, compressed upload.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2" x-show="formData.basic.video_intro_url">
                                <div class="flex items-center gap-3">
                                    <a :href="formData.basic.video_intro_url"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        View Video
                                    </a>
                                    <template x-if="isPremium">
                                        <button type="button"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition text-sm font-medium"
                                                @click="if (confirm('Delete your introduction video? This cannot be undone.')) deleteVideo()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7"></path>
                                            </svg>
                                            Delete Video
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('basic').then(() => nextStep())">
                                <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Education -->
                <div x-show="currentStep === 2" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Education Details</h2>
                        <button type="button" @click="addEducation()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Education
                        </button>
                    </div>
                    
                    <template x-for="(edu, index) in formData.education" :key="index">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 relative group">
                            <button type="button" @click="removeEducation(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Degree/Course</label>
                                    <input type="text" x-model="edu.degree" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. B.Tech, MBA">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Field of Study</label>
                                    <input type="text" x-model="edu.field_of_study" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Computer Science">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Institution/University</label>
                                    <input type="text" x-model="edu.institution" class="w-full rounded-lg border-gray-300 text-sm" placeholder="University Name">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Year of Passing</label>
                                    <input type="number" x-model="edu.end_date" class="w-full rounded-lg border-gray-300 text-sm" placeholder="YYYY">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Percentage/CGPA</label>
                                    <input type="text" x-model="edu.grade" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 85% or 8.5">
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="formData.education.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        No education details added yet. Click "Add Education" to start.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('education').then(() => nextStep())">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Experience -->
                <div x-show="currentStep === 3" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Work Experience</h2>
                        <button type="button" @click="addExperience()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Experience
                        </button>
                    </div>

                    <template x-for="(exp, index) in formData.experience" :key="index">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 relative">
                            <button type="button" @click="removeExperience(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Job Title</label>
                                    <input type="text" x-model="exp.job_title" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Software Engineer">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Company Name</label>
                                    <input type="text" x-model="exp.company_name" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Company Name">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                                    <input type="date" x-model="exp.start_date" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                                    <input type="date" x-model="exp.end_date" :disabled="exp.is_current" class="w-full rounded-lg border-gray-300 text-sm disabled:bg-gray-100">
                                    <div class="mt-1 flex items-center">
                                        <input type="checkbox" x-model="exp.is_current" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-xs text-gray-600">Currently Working</span>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                    <textarea x-model="exp.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Brief description of roles and responsibilities"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="formData.experience.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        No experience details added yet. Click "Add Experience" to start.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('experience').then(() => nextStep())">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Skills -->
                <div x-show="currentStep === 4" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Skills</h2>
                        <button type="button" @click="addSkill()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Skill
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(skill, index) in formData.skills" :key="index">
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <input type="text" x-model="skill.name" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Skill Name (e.g. PHP)">
                                </div>
                                <div class="w-32">
                                    <select x-model="skill.level" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="expert">Expert</option>
                                    </select>
                                </div>
                                <button type="button" @click="removeSkill(index)" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div x-show="formData.skills.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        Add your key skills to stand out to recruiters.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('skills').then(() => nextStep())">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 5: Languages -->
                <div x-show="currentStep === 5" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Languages</h2>
                        <button type="button" @click="addLanguage()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Language
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(lang, index) in formData.languages" :key="index">
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <input type="text" x-model="lang.language" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Language (e.g. English)">
                                </div>
                                <div class="w-40">
                                    <select x-model="lang.proficiency" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="basic">Basic</option>
                                        <option value="conversational">Conversational</option>
                                        <option value="fluent">Fluent</option>
                                        <option value="native">Native</option>
                                    </select>
                                </div>
                                <button type="button" @click="removeLanguage(index)" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('languages').then(() => nextStep())">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 6: Certificates -->
                <div x-show="currentStep === 6" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Certificates</h2>
                        <button type="button" @click="addCertificate()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Certificate
                        </button>
                    </div>

                    <template x-for="(cert, index) in formData.certificates" :key="index">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 relative">
                            <button type="button" @click="removeCertificate(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Certificate Name</label>
                                    <input type="text" x-model="cert.name" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. AWS Certified Solutions Architect">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Issuing Organization</label>
                                    <input type="text" x-model="cert.issuing_organization" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Amazon Web Services">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Issue Date</label>
                                    <input type="date" x-model="cert.issue_date" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Credential ID</label>
                                    <input type="text" x-model="cert.credential_id" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Optional">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Credential URL / Upload</label>
                                    <div class="flex gap-2 items-center">
                                        <input type="text" x-model="cert.credential_url" class="flex-1 rounded-lg border-gray-300 text-sm" placeholder="https://...">
                                        <div class="relative">
                                            <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                                @change="uploadFile('certificate', $event.target.files[0]).then(res => { if(res.url) cert.credential_url = res.url })">
                                            <button type="button" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                                Upload
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Enter URL or upload certificate file</p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="formData.certificates.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        Add your professional certificates and licenses.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('certificates').then(() => nextStep())">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 7: Employer Verification -->
                <div x-show="currentStep === 7" x-transition:enter="step-enter" class="space-y-6">
                    <div class="border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Employer Verification</h2>
                        <p class="text-sm text-gray-600 mt-1">Verify your previous employment to build trust with recruiters.</p>
                        <!-- Integrated verification within this step -->
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex items-center h-5">
                                <input type="checkbox" x-model="formData.verification.need_verification" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </div>
                            <div class="flex-1">
                                <label class="font-medium text-gray-900">I want to get Employer Verified</label>
                                <p class="text-sm text-gray-500 mt-1">Check this box to upload your previous company documents (Relieving Letter, Offer Letter, Salary Slips). These documents are optional but help in verifying your experience.</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="formData.verification.need_verification" class="space-y-6">
                        <!-- Experience Summary -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-100 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-semibold text-blue-900">Total Verified Experience</h3>
                                <p class="text-xs text-blue-700 mt-1">Calculated from verified employment blocks</p>
                            </div>
                            <div class="text-2xl font-bold text-indigo-600" x-text="totalVerifiedExperience"></div>
                        </div>

                        <!-- Employment Blocks -->
                        <template x-for="(emp, index) in formData.verification.employments" :key="index">
                            <div class="p-6 bg-gray-50 rounded-xl border border-gray-200 relative group transition-all hover:border-indigo-300">
                                <button type="button" @click="removeEmployment(index)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition p-1 hover:bg-white rounded-full shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <!-- Company Details -->
                                    <div class="space-y-4">
                                        <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            Company Details
                                        </h4>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Company Name</label>
                                            <input type="text" x-model="emp.company" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Google" @blur="saveEmploymentBlock(index)">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Job Title / Role</label>
                                            <input type="text" x-model="emp.role" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Senior Developer" @blur="saveEmploymentBlock(index)">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Employment Type</label>
                                            <select x-model="emp.type" class="w-full rounded-lg border-gray-300 text-sm">
                                                <option value="Full-time">Full-time</option>
                                                <option value="Part-time">Part-time</option>
                                                <option value="Contract">Contract</option>
                                                <option value="Internship">Internship</option>
                                            </select>
                                        </div>
                                        <div class="pt-2">
                                          <span class="text-xs text-gray-600">Status:</span>
                                          <template x-if="emp.status_overall === 'verified'">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 ml-2">Verified</span>
                                          </template>
                                          <template x-if="emp.status_overall === 'not_verified'">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 ml-2">Not Verified</span>
                                          </template>
                                          <template x-if="!emp.status_overall || emp.status_overall === 'under_review'">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 ml-2">Under Review</span>
                                          </template>
                                        </div>
                                    </div>

                                    <!-- Duration -->
                                    <div class="space-y-4">
                                        <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Duration
                                        </h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                                                <input type="date" x-model="emp.start_date" class="w-full rounded-lg border-gray-300 text-sm" @change="saveEmploymentBlock(index)">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                                                <input type="date" x-model="emp.end_date" :disabled="emp.is_current" class="w-full rounded-lg border-gray-300 text-sm disabled:bg-gray-100" @change="saveEmploymentBlock(index)">
                                            </div>
                                        </div>
                                        <div class="flex items-center pt-2">
                                            <input type="checkbox" x-model="emp.is_current" :id="'current-'+index" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" @change="saveEmploymentBlock(index)">
                                            <label :for="'current-'+index" class="ml-2 text-sm text-gray-600">Currently Working here</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Documents -->
                                <div class="border-t pt-4">
                                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Documents
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Helper for uploads -->
                                        <template x-for="docType in ['offer_letter', 'relieving_letter', 'experience_letter', 'salary_slip']">
                                            <div class="border rounded-lg p-3 bg-white relative hover:border-indigo-300 transition-colors" :key="docType">
                                                <label class="block text-xs font-medium text-gray-700 mb-2 capitalize" x-text="docType.replace('_', ' ')"></label>
                                                
                                                <!-- Upload State -->
                                                <div class="text-center p-3 border border-dashed border-gray-300 rounded hover:bg-gray-50 transition cursor-pointer relative">
                                                    <input type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                        @change="uploadEmploymentDocument(index, docType, $event.target.files[0])">
                                                    <div class="text-gray-400 flex flex-col items-center">
                                                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                        <span class="text-[10px]" x-show="!(emp.documentNames && emp.documentNames[docType])">Upload</span>
                                                        <div class="text-[10px] text-gray-600 truncate" x-show="emp.documentNames && emp.documentNames[docType]">
                                                            <span x-text="(emp.documentNames && emp.documentNames[docType]) ? emp.documentNames[docType] : ''"></span>
                                                            <span class="text-indigo-600" x-show="emp.uploadingDocs && emp.uploadingDocs[docType]"> • Uploading…</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Uploaded State -->
                                                <div x-show="emp.documents && emp.documents[docType]" class="bg-green-50 rounded border border-green-100 p-2">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="text-[10px] text-green-700 font-medium truncate" x-text="(emp.documentNames && emp.documentNames[docType]) ? emp.documentNames[docType] : 'Uploaded'"></span>
                                                        <button type="button" @click="emp.documents[docType] = null; if(emp.documentNames) emp.documentNames[docType]=null" class="text-red-400 hover:text-red-600">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <a :href="emp.documents[docType]" target="_blank" class="flex-1 text-center py-1 px-2 bg-white border border-green-200 rounded text-[10px] text-green-700 hover:bg-green-50 transition">
                                                            Preview
                                                        </a>
                                                    </div>
                                                    <div class="mt-2">
                                                        <template x-if="(() => { const u = emp.documents[docType] || ''; const e = (u.split('?')[0] || '').split('.').pop()?.toLowerCase(); return ['jpg','jpeg','png','gif','webp'].includes(e); })()">
                                                            <img :src="emp.documents[docType]" class="max-h-24 rounded border border-green-200">
                                                        </template>
                                                        <template x-if="(() => { const u = emp.documents[docType] || ''; const e = (u.split('?')[0] || '').split('.').pop()?.toLowerCase(); return e === 'pdf'; })()">
                                                            <iframe :src="emp.documents[docType]" class="w-full h-24 border border-green-200 rounded"></iframe>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between mt-1">
                                                    <div class="text-xs text-gray-700 truncate flex items-center gap-2">
                                                        <template x-if="emp.documents && emp.documents[docType]">
                                                            <span class="inline-flex items-center gap-2">
                                                                <a :href="emp.documents[docType]" target="_blank" class="hover:underline" x-text="(emp.documentNames && emp.documentNames[docType]) ? emp.documentNames[docType] : 'View file'"></a>
                                                                <span class="inline-flex items-center px-2 py-[2px] rounded-full bg-green-100 text-green-700 border border-green-200 text-[10px]">Uploaded</span>
                                                            </span>
                                                        </template>
                                                        <template x-if="!emp.documents?.[docType]">
                                                            <span class="inline-flex items-center px-2 py-[2px] rounded-full bg-gray-100 text-gray-600 border border-gray-200 text-[10px]">Not uploaded</span>
                                                        </template>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <button type="button" x-show="emp.documents && emp.documents[docType] && !(emp.uploadingDocs && emp.uploadingDocs[docType])" @click="replaceEmploymentDocument(index, docType)" class="text-[10px] text-indigo-600 hover:underline">Replace</button>
                                                        <span x-show="emp.uploadingDocs && emp.uploadingDocs[docType]" class="text-[10px] text-indigo-600">Uploading...</span>
                                                    </div>
                                                </div>
                                                <p x-show="emp.uploadError" class="text-[10px] text-red-600 mt-1" x-text="emp.uploadError"></p>
                                            </div>
                                        </template>
                                    </div>
                                    <!-- HR Contact + Consent + Request -->
                                    <div class="mt-6 space-y-3" x-show="formData.verification.need_verification">
                                      <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                          <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14M7 15h10"></path></svg>
                                          Verification
                                      </h4>
                                              <template x-if="emp.request && emp.status_overall !== 'verified'">
                                                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-800 flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span>Verification request pending</span>
                                                        <span x-show="emp.request.expires_at">• Expires in <span x-text="countdownText(emp.request.expires_at)"></span></span>
                                                    </div>
                                                    <div class="text-xs text-yellow-700">Token: <span class="font-mono" x-text="(emp.request.token||'').slice(0,8)+'…'"></span></div>
                                                </div>
                                              </template>
                                              <template x-if="emp.status_overall === 'verified'">
                                                <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    <span>Employment verified</span>
                                                </div>
                                              </template>
                                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                          <label class="block text-xs font-medium text-gray-500 mb-1">HR Official Email</label>
                                          <input type="email" x-model="emp.hr_email" class="w-full rounded-lg border-gray-300 text-sm" placeholder="hr@companydomain.com">
                                        </div>
                                        <div>
                                          <label class="block text-xs font-medium text-gray-500 mb-1">HR Phone (optional)</label>
                                          <input type="text" x-model="emp.hr_phone" class="w-full rounded-lg border-gray-300 text-sm" placeholder="+91-8800122222">
                                        </div>
                                        <div>
                                          <label class="block text-xs font-medium text-gray-500 mb-1">Reporting Manager Email (optional)</label>
                                          <input type="email" x-model="emp.manager_email" class="w-full rounded-lg border-gray-300 text-sm" placeholder="manager@companydomain.com">
                                        </div>
                                        <div>
                                          <label class="block text-xs font-medium text-gray-500 mb-1">Company Website (optional)</label>
                                          <input type="text" x-model="emp.company_website" class="w-full rounded-lg border-gray-300 text-sm" placeholder="https://www.companydomain.com">
                                        </div>
                                        <div>
                                          <label class="block text-xs font-medium text-gray-500 mb-1">CIN (optional)</label>
                                          <input type="text" x-model="emp.cin" class="w-full rounded-lg border-gray-300 text-sm" placeholder="GJ0000000000000000">
                                        </div>
                                        <div>
                                          <label class="block text-xs font-medium text-gray-500 mb-1">GST (optional)</label>
                                          <input type="text" x-model="emp.gst" class="w-full rounded-lg border-gray-300 text-sm" placeholder="27AAACF00000000">
                                        </div>
                                      </div>
                                      <div class="flex items-center gap-3 pt-2">
                                        <input type="checkbox" x-model="emp.consent" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                                        <span class="text-sm text-gray-700">I authorize the portal to contact previous employer for verification.</span>
                                      </div>
                                      <div class="pt-2">
                                                <button type="button" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 " :class="emp.requesting ? 'opacity-50 cursor-not-allowed' : ''" @click="if(!emp.requesting) requestVerification(index)">Request Verification</button>
                                      </div>
                                      <div class="pt-2 text-xs text-gray-600 space-y-1">
                                        <div class="flex items-center gap-2"><span :class="emp.docsUploaded ? 'text-green-600' : 'text-gray-500'">📄 Documents Uploaded</span></div>
                                        <div class="flex items-center gap-2"><span :class="emp.hr_email ? 'text-green-600' : 'text-gray-500'">📧 HR Email Added</span></div>
                                        <div class="flex items-center gap-2"><span :class="emp.emailSent ? 'text-green-600' : 'text-gray-500'">🕒 Verification Email Sent</span></div>
                                        <div class="flex items-center gap-2"><span :class="emp.hrResponded ? 'text-green-600' : 'text-gray-500'">📩 HR Response Pending</span></div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Add Button -->
                        <button type="button" @click="addEmployment()" class="w-full py-3 border-2 border-dashed border-indigo-200 rounded-xl text-indigo-600 font-medium hover:bg-indigo-50 hover:border-indigo-400 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Another Employment
                        </button>
                    </div>

                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('verification').then(() => nextStep())">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Continue'"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 8: Additional -->
                <div x-show="currentStep === 8" x-transition:enter="step-enter" class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4">Additional Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Salary (Min)</label>
                            <input type="number" x-model="formData.additional.expected_salary_min" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Annual Salary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Salary (Max)</label>
                            <input type="number" x-model="formData.additional.expected_salary_max" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Annual Salary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Salary</label>
                            <input type="number" x-model="formData.additional.current_salary" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Current Annual Salary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notice Period</label>
                            <select x-model="formData.additional.notice_period" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">Select Notice Period</option>
                                <option value="Immediate">Immediate</option>
                                <option value="15 Days">15 Days</option>
                                <option value="30 Days">30 Days</option>
                                <option value="60 Days">60 Days</option>
                                <option value="90 Days">90 Days</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Location</label>
                            <input type="text" x-model="formData.additional.preferred_job_location" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="e.g. Bangalore, Remote">
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold text-gray-800">Social Links</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                                <input type="url" x-model="formData.additional.linkedin_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://linkedin.com/in/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">GitHub URL</label>
                                <input type="url" x-model="formData.additional.github_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://github.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Portfolio/Website</label>
                                <input type="url" x-model="formData.additional.portfolio_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md disabled:opacity-50 flex items-center gap-2" :disabled="saving" @click="saveSection('additional')">
                            <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="saving ? 'Saving...' : 'Save & Complete Profile'"></span>
                        </button>
                    </div>
                </div>

                    </form>
                </div>
                <aside class="lg:w-80 flex-shrink-0 space-y-6">
                    <div class="card p-6 top-24">
                        <h3 class="card-title mb-3">Completion Insights</h3>
                        <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center gap-4">
                            <div class="relative flex-shrink-0" style="width: 200px; height:200px;">
                                <svg class="w-full h-full" viewBox="45 20 200 160" style="cursor: default;">
                                    <g x-ref="pieLayer"></g>
                                    <text x="145" y="105" text-anchor="middle" dominant-baseline="middle" style="font-weight:700;fill:#111827;font-size:22px" x-text="overallPercent + '%'"></text>
                                </svg>
                                <div class="chart-tooltip" x-show="tipVisible" :style="'transform:translate('+tipX+'px,'+tipY+'px)'">
                                    <span x-text="tipText"></span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div class="flex items-center gap-2"><span class="inline-block w-2 h-2 rounded-full" style="background:#10B981"></span>Completed</div>
                                <div class="flex items-center gap-2"><span class="inline-block w-2 h-2 rounded-full" style="background:#F59E0B"></span>In Progress</div>
                                <div class="flex items-center gap-2"><span class="inline-block w-2 h-2 rounded-full" style="background:#EF4444"></span>Missing</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="w-full h-2 rounded-full overflow-hidden bg-gray-200 flex">
                                <div class="h-2 bg-green-500" :style="'width:'+completionBreakdown.completePct+'%'"></div>
                                <div class="h-2 bg-yellow-400" :style="'width:'+completionBreakdown.inProgressPct+'%'"></div>
                                <div class="h-2 bg-gray-300 flex-1" :style="'width:'+completionBreakdown.missingPct+'%'"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 mt-1">
                                <span x-text="'Complete: '+completionBreakdown.complete"></span>
                                <span x-text="'In Progress: '+completionBreakdown.inProgress"></span>
                                <span x-text="'Missing: '+completionBreakdown.missing"></span>
                            </div>
                        </div>
                    </div>
                    <div class="card p-6">
                        <h3 class="card-title mb-3">Section Breakdown</h3>
                        <template x-for="(label, key) in {basic:'Basic Details',education:'Education',experience:'Experience',skills:'Skills',languages:'Languages',certificates:'Certificates',verification:'Verification',additional:'Additional'}">
                            <div class="mb-3">
                                <div class="flex justify-between text-xs mb-1">
                                    <span x-text="label"></span>
                                    <span x-text="sectionPercentages[key] + '%'"></span>
                                </div>
                                <div class="sb-bar">
                                    <div class="sb-fill" :style="'width:'+sectionPercentages[key]+'%;background:'+sectionColor(key)"></div>
                                    <div class="sb-knob" :style="'left:'+sectionPercentages[key]+'%;background:'+sectionColor(key)"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
                        <h4 class="font-semibold mb-2">💡 Profile Tips</h4>
                        <ul class="space-y-2 text-xs text-gray-900">
                            <li class="flex gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check h-3.5 w-3.5 mt-0.5 shrink-0"><path d="M20 6 9 17l-5-5"></path></svg> Complete all sections for 5x more visibility</li>
                            <li class="flex gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check h-3.5 w-3.5 mt-0.5 shrink-0"><path d="M20 6 9 17l-5-5"></path></svg> Add certificates to stand out</li>
                            <li class="flex gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check h-3.5 w-3.5 mt-0.5 shrink-0"><path d="M20 6 9 17l-5-5"></path></svg> Verified profiles get priority in search</li>
                        </ul>
                    </div>
                </aside>
            </div>
            <!-- Toasts (inside Alpine scope) -->
            <div class="toast" x-show="toasts && toasts.length">
                <template x-for="t in toasts">
                    <div class="item" :class="t.type">
                        <span x-text="t.msg"></span>
                    </div>
                </template>
            </div>
        </div>
    <?php include __DIR__ . '/../include/footer.php'; ?>
</body>
</html>