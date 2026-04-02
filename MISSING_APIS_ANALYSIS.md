# Job Portal Mobile App - Missing APIs Analysis

## Current State Analysis

### ✅ Existing Endpoints (21 endpoints)
1. **Authentication** (3)
   - POST /login
   - POST /register-candidate
   - POST /register-employer

2. **Jobs** (3)
   - GET /jobs (public)
   - GET /jobs/{slug} (public)
   - POST /jobs/{id}/apply (authenticated)

3. **Employer Jobs** (4)
   - GET /employer/jobs
   - POST /employer/jobs
   - PUT /employer/jobs/{id}
   - DELETE /employer/jobs/{id}

4. **Profile** (2)
   - GET /profile
   - PUT /profile

5. **Dashboard** (1)
   - GET /dashboard

6. **Notifications** (3)
   - GET /notifications
   - POST /notifications/{id}/read
   - POST /notifications/read-all

---

## ❌ Missing Critical APIs (+ 89 endpoints required)

### 1. AUTHENTICATION & AUTH MANAGEMENT (11 endpoints)
- [x] Login - EXISTS
- [x] Register Candidate - EXISTS
- [x] Register Employer - EXISTS
- **MISSING:**
  - POST /logout (token blacklisting)
  - POST /refresh-token (token refresh)
  - POST /forgot-password (initiate reset)
  - POST /reset-password (complete reset)
  - POST /verify-email (email verification)
  - POST /verify-otp (OTP verification)
  - POST /resend-otp (resend OTP)
  - POST /change-password (authenticated)
  - GET /auth/google/callback (OAuth)
  - GET /auth/apple/callback (OAuth)

### 2. RESUME & FILE MANAGEMENT (12 endpoints)
- **MISSING:**
  - POST /resumes/upload (multipart file upload)
  - GET /resumes (list user resumes)
  - GET /resumes/{id} (get resume details)
  - PUT /resumes/{id} (update resume)
  - DELETE /resumes/{id} (delete resume)
  - POST /resumes/{id}/parse (AI parse)
  - POST /resumes/{id}/download (download as PDF)
  - GET /resumes/{id}/preview (preview content)
  - POST /resumes/create-from-profile (auto-generate)
  - PUT /resumes/{id}/set-default (set as primary)
  - GET /resume-templates (list templates)
  - POST /resumes/{id}/export (export options)

### 3. CANDIDATE PROFILE MANAGEMENT (9 endpoints)
- **MISSING:**
  - GET /profile/candidate/detailed (full profile)
  - POST /profile/education (add education)
  - PUT /profile/education/{id} (update education)
  - DELETE /profile/education/{id} (delete education)
  - POST /profile/experience (add experience)
  - PUT /profile/experience/{id} (update experience)
  - DELETE /profile/experience/{id} (delete experience)
  - POST /profile/skills (add skill)
  - DELETE /profile/skills/{id} (remove skill)
  - POST /profile/languages (add language)
  - DELETE /profile/languages/{id} (remove language)
  - POST /profile/interests (set job interests)
  - GET /profile/completion-status (profile completion %)

### 4. APPLICATIONS MANAGEMENT (8 endpoints)
- **MISSING:**
  - GET /applications (list my applications)
  - GET /applications/{id} (get detailed application)
  - POST /applications/{id}/withdraw (withdraw application)
  - GET /applications?status=pending (filter by status)
  - POST /applications/{id}/accept-offer (accept job offer)
  - POST /applications/{id}/reject-offer (reject offer)
  - GET /employer/applications (list received applications)
  - GET /employer/applications/{id}/resume (download candidate resume)

### 5. JOB BOOKMARKS (4 endpoints)
- **MISSING:**
  - POST /jobs/{id}/bookmark (save job)
  - DELETE /jobs/{id}/bookmark (remove bookmark)
  - GET /bookmarks (list saved jobs)
  - POST /bookmarks/bulk-delete (bulk delete)

### 6. COMPANY PROFILE MANAGEMENT (9 endpoints)
- **MISSING:**
  - GET /company/profile (view company details)
  - PUT /company/profile (update company info)
  - POST /company/logo (upload logo)
  - POST /company/banner (upload banner)
  - POST /company/documents/upload (KYC docs)
  - GET /company/documents (list uploaded docs)
  - DELETE /company/documents/{id} (delete doc)
  - GET /company/verification-status (KYC status)
  - POST /company/social-links (add social links)

### 7. REVIEWS & RATINGS (6 endpoints)
- **MISSING:**
  - GET /reviews/company/{id} (list company reviews)
  - POST /reviews (create review - after hire)
  - GET /reviews/my-reviews (reviews I gave)
  - PUT /reviews/{id} (update review)
  - DELETE /reviews/{id} (delete review)
  - GET /reviews/company/{id}/stats (review statistics)

### 8. JOB SEARCH & FILTERING (6 endpoints)
- **MISSING:**
  - GET /jobs/search (advanced search)
  - GET /jobs/search/filters (available filters)
  - POST /jobs/search-saved (save search)
  - GET /saved-searches (list saved searches)
  - DELETE /saved-searches/{id} (delete search)
  - GET /jobs/trending (trending jobs)

### 9. EMPLOYER CANDIDATE SEARCH (5 endpoints)
- **MISSING:**
  - GET /candidates (search candidates)
  - GET /candidates/{id} (view candidate profile)
  - POST /candidates/{id}/invite (send job invite)
  - POST /candidates/{id}/shortlist (shortlist)
  - GET /shortlists (view shortlisted candidates)

### 10. CHAT & MESSAGE SYSTEM (11 endpoints)
- **MISSING:**
  - GET /conversations (list conversations)
  - POST /conversations (create new conversation)
  - GET /conversations/{id}/messages (get messages)
  - POST /conversations/{id}/messages (send message)
  - DELETE /conversations/{id}/messages/{msg_id} (delete message)
  - PATCH /conversations/{id}/messages/{msg_id} (edit message)
  - POST /conversations/{id}/read (mark as read)
  - DELETE /conversations/{id} (delete conversation)
  - POST /conversations/{id}/block (block user)
  - POST /conversations/{id}/archive (archive)
  - GET /conversations/unread-count (unread count)

### 11. INTERVIEW SCHEDULING (10 endpoints)
- **MISSING:**
  - POST /interviews/schedule (schedule interview)
  - GET /interviews (list interviews)
  - GET /interviews/{id} (interview details)
  - PUT /interviews/{id} (update interview)
  - DELETE /interviews/{id} (cancel interview)
  - POST /interviews/{id}/reschedule (reschedule)
  - POST /interviews/{id}/complete (mark complete)
  - POST /interviews/{id}/feedback (add feedback)
  - GET /interviews/{id}/jitsi-token (video call token)
  - POST /interviews/{id}/attendance (mark attendance)

### 12. PAYMENTS & SUBSCRIPTIONS (14 endpoints)
- **MISSING:**
  - GET /subscription-plans (list plans)
  - POST /payments/initiate (start payment)
  - POST /payments/verify (verify payment)
  - GET /payments/history (payment history)
  - POST /subscription/upgrade (upgrade plan)
  - POST /subscription/cancel (cancel subscription)
  - GET /subscription/current (current subscription)
  - POST /payments/refund (request refund)
  - POST /payments/razorpay/webhook (Razorpay webhook)
  - POST /payments/cashfree/webhook (Cashfree webhook)
  - GET /invoices (list invoices)
  - GET /invoices/{id}/download (download invoice)
  - POST /payments/wallet/add (wallet topup)
  - GET /payments/wallet/balance (wallet balance)

### 13. NOTIFICATIONS MANAGEMENT (6 endpoints)
- **MISSING:**
  - POST /notifications/fcm-token (register FCM token)
  - DELETE /notifications/fcm-token (unregister token)
  - GET /notifications/preferences (notification settings)
  - PUT /notifications/preferences (update preferences)
  - POST /notifications/test (test notification)
  - GET /notifications/history (notification history)

### 14. ANALYTICS & TRACKING (8 endpoints)
- **MISSING:**
  - GET /analytics/candidates/profile-views (who viewed profile)
  - GET /analytics/candidates/job-applications (application stats)
  - POST /analytics/candidates/tracking (job searches)
  - GET /analytics/employers/job-stats (job performance)
  - GET /analytics/employers/candidates-views (candidate search history)
  - POST /analytics/event (track custom events)
  - GET /analytics/dashboard (analytics summary)
  - GET /analytics/job/{id}/stats (job-specific stats)

### 15. JOB ALERTS (5 endpoints)
- **MISSING:**
  - POST /job-alerts (create alert)
  - GET /job-alerts (list my alerts)
  - PUT /job-alerts/{id} (update alert)
  - DELETE /job-alerts/{id} (delete alert)
  - GET /job-alerts/{id}/count (matching jobs count)

### 16. EMPLOYER DASHBOARD (7 endpoints)
- **MISSING:**
  - GET /employer/dashboard/stats (stats summary)
  - GET /employer/dashboard/recent-applications (recent apps)
  - GET /employer/dashboard/active-jobs (active job count)
  - GET /employer/dashboard/upcoming-interviews (upcoming)
  - GET /employer/dashboard/team-members (team list)
  - POST /employer/team-members (add team member)
  - PUT /employer/team-members/{id} (update member)

### 17. UTILITY ENDPOINTS (8 endpoints)
- **MISSING:**
  - GET /locations (list all locations/cities)
  - GET /job-titles (list job titles autocomplete)
  - GET /skills (list skills autocomplete)
  - GET /companies (list companies)
  - POST /feedback (send feedback)
  - POST /report (report user/job)
  - GET /app-version (check app version)
  - GET /maintenance-status (check app status)

---

## Summary Statistics

| Category | Existing | Missing | Total |
|----------|----------|---------|-------|
| Authentication | 3 | 7 | 10 |
| Resume | 0 | 12 | 12 |
| Candidate Profile | 2 | 11 | 13 |
| Applications | 1 | 7 | 8 |
| Bookmarks | 0 | 4 | 4 |
| Company Profile | 0 | 9 | 9 |
| Reviews | 0 | 6 | 6 |
| Job Search | 2 | 5 | 7 |
| Candidate Search | 0 | 5 | 5 |
| Chat System | 0 | 11 | 11 |
| Interviews | 0 | 10 | 10 |
| Payments | 0 | 14 | 14 |
| Notifications | 3 | 6 | 9 |
| Analytics | 0 | 8 | 8 |
| Job Alerts | 0 | 5 | 5 |
| Employer Dashboard | 1 | 7 | 8 |
| Utility | 0 | 8 | 8 |
| **TOTAL** | **21** | **127** | **148** |

---

## Architecture Overview

### Key Requirements Met:
✅ JWT Authentication on all protected routes
✅ Standard JSON Response Format
✅ Mobile App Compatibility (pagination, filtering)
✅ Consistent Error Handling
✅ Request Validation
✅ Proper HTTP Status Codes

### Response Structure:
```json
{
  "success": true/false,
  "message": "string",
  "data": {},
  "errors": {}
}
```

### Error Codes Used:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

---

## Implementation Priority

**Phase 1 (Critical - Week 1):**
1. Auth Management (logout, refresh-token, forgot-password)
2. Resume Management (upload, list, delete)
3. Applications (list, withdraw)
4. Payments (initiate, verify)

**Phase 2 (High - Week 2-3):**
1. Chat System
2. Interview Scheduling
3. Company Profile
4. Candidate Detailed Profile

**Phase 3 (Medium - Week 4):**
1. Reviews System
2. Employer Dashboard
3. Analytics
4. Job Alerts

**Phase 4 (Nice to Have):**
1. Advanced Search/Filtering
2. Candidate Search (for employers)
3. Utility endpoints
