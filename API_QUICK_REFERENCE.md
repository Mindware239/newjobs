# Complete Job Portal Mobile App API - Quick Reference

## 📊 API Overview

**Total Endpoints:** 148  
**Completed:** 21 (14%)  
**New Implementation:** 127 (86%)  
**Authentication:** JWT (All protected routes)  
**Response Format:** Standard JSON with success/error structure

---

## 🚀 AUTHENTICATION (10 Endpoints)

| Method | Endpoint | Auth | Status | Response |
|--------|----------|------|--------|----------|
| POST | `/login` | ❌ | ✅ | `{token, user}` |
| POST | `/register-candidate` | ❌ | ✅ | `{user}` |
| POST | `/register-employer` | ❌ | ✅ | `{user}` |
| POST | `/logout` | ✅ | 🆕 | `{}` |
| POST | `/refresh-token` | ✅ | 🆕 | `{token}` |
| POST | `/verify-email` | ❌ | 🆕 | `{}` |
| POST | `/verify-otp` | ❌ | 🆕 | `{}` |
| POST | `/forgot-password` | ❌ | 🆕 | `{reset_token}` |
| POST | `/reset-password` | ❌ | 🆕 | `{}` |
| POST | `/change-password` | ✅ | 🆕 | `{}` |
| GET | `/me` | ✅ | ✅ | `{user}` |

---

## 📄 CANDIDATE PROFILE (13 Endpoints)

| Method | Endpoint | Auth | Controller | Status |
|--------|----------|------|------------|--------|
| GET | `/candidate/profile/detailed` | ✅ | ProfileController | 🆕 |
| PUT | `/profile` | ✅ | ProfileController | ✅ |
| GET | `/profile` | ✅ | ProfileController | ✅ |
| POST | `/profile/avatar` | ✅ | ProfileController | 🆕 |
| DELETE | `/profile/avatar` | ✅ | ProfileController | 🆕 |
| POST | `/candidate/profile/education` | ✅ | CandidateProfileController | ✅ |
| PUT | `/candidate/profile/education/{id}` | ✅ | CandidateProfileController | ✅ |
| DELETE | `/candidate/profile/education/{id}` | ✅ | CandidateProfileController | ✅ |
| POST | `/candidate/profile/experience` | ✅ | CandidateProfileController | ✅ |
| PUT | `/candidate/profile/experience/{id}` | ✅ | CandidateProfileController | ✅ |
| DELETE | `/candidate/profile/experience/{id}` | ✅ | CandidateProfileController | ✅ |
| POST | `/candidate/profile/skills` | ✅ | CandidateProfileController | ✅ |
| DELETE | `/candidate/profile/skills/{id}` | ✅ | CandidateProfileController | ✅ |
| POST | `/candidate/profile/languages` | ✅ | CandidateProfileController | ✅ |
| DELETE | `/candidate/profile/languages/{id}` | ✅ | CandidateProfileController | ✅ |
| POST | `/candidate/profile/interests` | ✅ | CandidateProfileController | ✅ |
| GET | `/candidate/profile/completion-status` | ✅ | CandidateProfileController | ✅ |

---

## 📋 RESUME MANAGEMENT (12 Endpoints)

| Method | Endpoint | Max Size | Status |
|--------|----------|----------|--------|
| POST | `/candidate/resumes/upload` | 10MB | ✅ |
| GET | `/candidate/resumes` | - | ✅ |
| GET | `/candidate/resumes/{id}` | - | ✅ |
| PUT | `/candidate/resumes/{id}` | - | ✅ |
| DELETE | `/candidate/resumes/{id}` | - | ✅ |
| POST | `/candidate/resumes/{id}/parse` | - | ✅ |
| POST | `/candidate/resumes/{id}/download` | - | ✅ |
| GET | `/candidate/resumes/{id}/preview` | - | ✅ |
| POST | `/candidate/resumes/create-from-profile` | - | ✅ |
| PUT | `/candidate/resumes/{id}/set-default` | - | ✅ |
| GET | `/candidate/resume-templates` | - | ✅ |
| POST | `/candidate/resumes/{id}/export` | - | ✅ |

**Supported Formats:** PDF, DOCX, TXT  
**Default Resume:** Only 1 per candidate

---

## 💼 JOB MANAGEMENT (7 Endpoints)

| Method | Endpoint | Auth | Controller | Status |
|--------|----------|------|------------|--------|
| GET | `/jobs` | ❌ | JobController | ✅ |
| GET | `/jobs/{slug}` | ❌ | JobController | ✅ |
| GET | `/jobs/search` | ❌ | JobController | 🆕 |
| GET | `/jobs/search/filters` | ❌ | JobController | 🆕 |
| POST | `/jobs/{id}/apply` | ✅ | JobController | ✅ |
| GET | `/jobs/trending` | ❌ | JobController | 🆕 |
| POST | `/jobs/search-saved` | ✅ | JobController | 🆕 |
| GET | `/saved-searches` | ✅ | JobController | 🆕 |
| DELETE | `/saved-searches/{id}` | ✅ | JobController | 🆕 |

---

## 🔖 JOB BOOKMARKS (4 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| POST | `/candidate/jobs/{id}/bookmark` | BookmarkController | ✅ |
| DELETE | `/candidate/jobs/{id}/bookmark` | BookmarkController | ✅ |
| GET | `/candidate/bookmarks` | BookmarkController | ✅ |
| POST | `/candidate/bookmarks/bulk-delete` | BookmarkController | ✅ |

---

## 📲 APPLICATIONS (8 Endpoints)

| Method | Endpoint | Auth | Role | Status |
|--------|----------|------|------|--------|
| GET | `/candidate/applications` | ✅ | Candidate | ✅ |
| GET | `/candidate/applications/{id}` | ✅ | Candidate | ✅ |
| POST | `/candidate/applications/{id}/withdraw` | ✅ | Candidate | ✅ |
| POST | `/candidate/applications/{id}/accept-offer` | ✅ | Candidate | ✅ |
| POST | `/candidate/applications/{id}/reject-offer` | ✅ | Candidate | ✅ |
| GET | `/employer/applications` | ✅ | Employer | ✅ |
| POST | `/employer/applications/{id}/shortlist` | ✅ | Employer | ✅ |
| POST | `/employer/applications/{id}/reject` | ✅ | Employer | ✅ |
| POST | `/employer/applications/{id}/send-offer` | ✅ | Employer | ✅ |
| GET | `/employer/applications/{id}/resume` | ✅ | Employer | ✅ |

---

## 💬 CHAT & MESSAGING (11 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/conversations` | ChatController | ✅ |
| POST | `/conversations` | ChatController | ✅ |
| GET | `/conversations/{id}/messages` | ChatController | ✅ |
| POST | `/conversations/{id}/messages` | ChatController | ✅ |
| DELETE | `/conversations/{id}/messages/{msg_id}` | ChatController | ✅ |
| PATCH | `/conversations/{id}/messages/{msg_id}` | ChatController | ✅ |
| POST | `/conversations/{id}/read` | ChatController | ✅ |
| DELETE | `/conversations/{id}` | ChatController | ✅ |
| POST | `/conversations/{id}/block` | ChatController | ✅ |
| POST | `/conversations/{id}/archive` | ChatController | ✅ |
| GET | `/conversations/unread-count` | ChatController | ✅ |

**Features:**
- Real-time online/offline indicators
- Message read receipts
- Block/Unblock users
- Archive conversations
- Support for text, images, files

---

## 📅 INTERVIEWS (10 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| POST | `/interviews/schedule` | InterviewController | ✅ |
| GET | `/interviews` | InterviewController | ✅ |
| GET | `/interviews/{id}` | InterviewController | ✅ |
| PUT | `/interviews/{id}` | InterviewController | ✅ |
| DELETE | `/interviews/{id}` | InterviewController | ✅ |
| POST | `/interviews/{id}/reschedule` | InterviewController | ✅ |
| POST | `/interviews/{id}/complete` | InterviewController | ✅ |
| POST | `/interviews/{id}/feedback` | InterviewController | ✅ |
| GET | `/interviews/{id}/jitsi-token` | InterviewController | ✅ |
| POST | `/interviews/{id}/attendance` | InterviewController | ✅ |

**Interview Types:** Phone, Video, In-Person, Online  
**Video Integration:** Jitsi Meet

---

## 💰 PAYMENTS & SUBSCRIPTIONS (14 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/subscription-plans` | PaymentController | ✅ |
| POST | `/payments/initiate` | PaymentController | ✅ |
| POST | `/payments/verify` | PaymentController | ✅ |
| GET | `/payments/history` | PaymentController | ✅ |
| POST | `/subscription/upgrade` | PaymentController | ✅ |
| POST | `/subscription/cancel` | PaymentController | ✅ |
| GET | `/subscription/current` | PaymentController | ✅ |
| POST | `/payments/refund` | PaymentController | ✅ |
| POST | `/payments/razorpay/webhook` | PaymentController | ✅ |
| POST | `/payments/cashfree/webhook` | PaymentController | ✅ |
| GET | `/invoices` | PaymentController | ✅ |
| GET | `/invoices/{id}/download` | PaymentController | ✅ |
| POST | `/payments/wallet/add` | PaymentController | ✅ |
| GET | `/payments/wallet/balance` | PaymentController | ✅ |

**Payment Gateways:**
- Razorpay (UPI, Cards, Netbanking)
- Cashfree
- Stripe (optional)

**Features:**
- Subscription plans with auto-renewal
- Proration on upgrades
- Invoice generation
- Wallet system
- Refund management

---

## 🔔 NOTIFICATIONS (9 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/notifications` | NotificationController | ✅ |
| POST | `/notifications/{id}/read` | NotificationController | ✅ |
| POST | `/notifications/read-all` | NotificationController | ✅ |
| POST | `/notifications/fcm-token` | NotificationController | ✅ |
| DELETE | `/notifications/fcm-token` | NotificationController | ✅ |
| GET | `/notifications/preferences` | NotificationController | ✅ |
| PUT | `/notifications/preferences` | NotificationController | ✅ |
| POST | `/notifications/test` | NotificationController | ✅ |
| GET | `/notifications/history` | NotificationController | ✅ |

**Notification Types:**
- Application Status (submitted, shortlisted, rejected, offered)
- Interview Alerts
- Message Notifications
- Job Matching Alerts
- Payment Confirmations
- Profile & Company Updates

---

## ⭐ REVIEWS & RATINGS (6 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/reviews/company/{id}` | ReviewController | ✅ |
| POST | `/reviews` | ReviewController | ✅ |
| GET | `/reviews/my-reviews` | ReviewController | ✅ |
| PUT | `/reviews/{id}` | ReviewController | ✅ |
| DELETE | `/reviews/{id}` | ReviewController | ✅ |
| GET | `/reviews/company/{id}/stats` | ReviewController | ✅ |

**Rating Scale:** 1-5 stars  
**Edit Window:** 30 days  
**Sort Options:** Latest, Helpful, Rating (High/Low)

---

## 📊 ANALYTICS (8 Endpoints)

| Method | Endpoint | Role | Status |
|--------|----------|------|--------|
| GET | `/analytics/dashboard` | Both | ✅ |
| GET | `/analytics/profile-views` | Candidate | ✅ |
| GET | `/analytics/applications` | Candidate | ✅ |
| GET | `/analytics/job/{id}/stats` | Employer | ✅ |
| GET | `/analytics/job-stats` | Employer | ✅ |
| GET | `/analytics/candidates-views` | Employer | ✅ |
| POST | `/analytics/event` | Both | ✅ |
| GET | `/analytics/dashboard/stats` | Employer | ✅ |

---

## 🔍 JOB SEARCH & ALERTS (5 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| POST | `/candidate/job-alerts` | AlertController | ✅ |
| GET | `/candidate/job-alerts` | AlertController | ✅ |
| PUT | `/candidate/job-alerts/{id}` | AlertController | ✅ |
| DELETE | `/candidate/job-alerts/{id}` | AlertController | ✅ |
| GET | `/candidate/job-alerts/{id}/count` | AlertController | ✅ |

**Alert Frequency:** Daily, Weekly, Instant

---

## 🏢 EMPLOYER FEATURES (7 Endpoints + Job Mgmt)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/employer/profile` | EmployerProfileController | 🆕 |
| PUT | `/employer/profile` | EmployerProfileController | 🆕 |
| POST | `/employer/profile/logo` | EmployerProfileController | 🆕 |
| POST | `/employer/profile/banner` | EmployerProfileController | 🆕 |
| POST | `/employer/profile/documents/upload` | EmployerProfileController | 🆕 |
| GET | `/employer/profile/documents` | EmployerProfileController | 🆕 |
| DELETE | `/employer/profile/documents/{id}` | EmployerProfileController | 🆕 |
| GET | `/employer/profile/verification-status` | EmployerProfileController | 🆕 |
| POST | `/employer/profile/social-links` | EmployerProfileController | 🆕 |

---

## 👥 EMPLOYER - CANDIDATE SEARCH (5 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/employer/candidates` | CandidateController | 🆕 |
| GET | `/employer/candidates/{id}` | CandidateController | 🆕 |
| POST | `/employer/candidates/{id}/invite` | CandidateController | 🆕 |
| POST | `/employer/candidates/{id}/shortlist` | CandidateController | 🆕 |
| GET | `/employer/shortlists` | CandidateController | 🆕 |

**Filters:**
- Job Title
- Experience Level
- Location
- Skills
- Availability

---

## 📈 EMPLOYER DASHBOARD (7 Endpoints)

| Method | Endpoint | Controller | Status |
|--------|----------|------------|--------|
| GET | `/employer/dashboard` | DashboardController | 🆕 |
| GET | `/employer/dashboard/stats` | DashboardController | 🆕 |
| GET | `/employer/dashboard/recent-applications` | DashboardController | 🆕 |
| GET | `/employer/dashboard/active-jobs` | DashboardController | 🆕 |
| GET | `/employer/dashboard/upcoming-interviews` | DashboardController | 🆕 |
| GET | `/employer/team-members` | DashboardController | 🆕 |
| POST | `/employer/team-members` | DashboardController | 🆕 |
| PUT | `/employer/team-members/{id}` | DashboardController | 🆕 |
| DELETE | `/employer/team-members/{id}` | DashboardController | 🆕 |

---

## ⚙️ UTILITY & PUBLIC (8 Endpoints)

| Method | Endpoint | Auth | Status |
|--------|----------|------|--------|
| GET | `/locations` | ❌ | ✅ |
| GET | `/job-titles` | ❌ | ✅ |
| GET | `/skills` | ❌ | ✅ |
| GET | `/companies` | ❌ | ✅ |
| POST | `/feedback` | ✅ | ✅ |
| POST | `/report` | ✅ | ✅ |
| GET | `/app-version` | ❌ | ✅ |
| GET | `/maintenance-status` | ❌ | ✅ |

---

## 📦 QUICK IMPLEMENTATION STEPS

### 1. Deploy New Routes
```bash
cp routes/api_v1_complete.php routes/api_v1.php
```

### 2. Create Controller Files
All provided controller code is ready to copy:
- ✅ ResumeController.php
- ✅ ApplicationController.php  
- ✅ ChatController.php
- ✅ InterviewController.php
- ✅ PaymentController.php
- ✅ CandidateProfileController.php
- ✅ BookmarkController.php
- ✅ ReviewController.php
- ✅ AnalyticsController.php
- ✅ UtilityController.php
- 🆕 Employer/* controllers (templates provided)

### 3. Create Models & Migrations
See `API_IMPLEMENTATION_GUIDE.md` for complete database setup

### 4. Run Tests
Use provided Postman collection or cURL examples

### 5. Deploy to Production

---

## 🎯 Key Features Summary

✅ **JWT Authentication** - Secure token-based auth  
✅ **File Management** - Resume upload with parsing  
✅ **Real-time Chat** - Conversations with read receipts  
✅ **Video Interviews** - Jitsi Meet integration  
✅ **Payments** - Razorpay & Cashfree support  
✅ **Push Notifications** - FCM integration  
✅ **Job Alerts** - Smart matching engine  
✅ **Analytics** - Detailed tracking  
✅ **Mobile Optimized** - Pagination, filtering  
✅ **Standard JSON** - Consistent responses  

---

## 📞 Support

For detailed implementation guides, refer to:
- `MISSING_APIS_ANALYSIS.md` - Gap analysis
- `API_IMPLEMENTATION_GUIDE.md` - Complete code examples
- `routes/api_v1_complete.php` - Full route structure

All endpoints follow best practices for mobile app development with proper error handling, validation, and performance optimization.
