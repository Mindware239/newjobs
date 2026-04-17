# Missing APIs in Swagger Audit

## 1. Authentication
- `/api/v1/send-phone-otp` (POST)
- `/api/v1/login-phone` (POST)
- `/api/v1/register-candidate-phone` (POST)
- `/api/v1/register-employer-phone` (POST)
- `/api/v1/verify-email` (POST)
- `/api/v1/resend-otp` (POST)
- `/api/v1/auth/google/callback` (POST)
- `/api/v1/auth/apple/callback` (POST)
- `/api/v1/refresh-token` (POST)

## 2. Employer Panel (Critical Missing)
- `/api/v1/employer/profile/logo` (POST)
- `/api/v1/employer/profile/banner` (POST)
- `/api/v1/employer/profile/documents/upload` (POST)
- `/api/v1/employer/profile/social-links` (POST)
- `/api/v1/employer/jobs/{id}/duplicate` (POST)
- `/api/v1/employer/jobs/{id}/publish` (POST)
- `/api/v1/employer/jobs/{id}/unpublish` (POST)
- `/api/v1/employer/applications/{id}/resume` (GET)
- `/api/v1/employer/applications/{id}/send-offer` (POST)
- `/api/v1/employer/candidates` (GET)
- `/api/v1/employer/candidates/{id}/invite` (POST)
- `/api/v1/employer/team-members` (GET, POST, PUT, DELETE)
- `/api/v1/employer/dashboard/stats` (GET)
- `/api/v1/employer/dashboard/recent-applications` (GET)
- `/api/v1/employer/dashboard/active-jobs` (GET)
- `/api/v1/employer/dashboard/upcoming-interviews` (GET)

## 3. Shared Modules (Chat, Interviews, Notifications)
- **ALL Chat APIs are missing from Swagger**
- **ALL Interview APIs are missing from Swagger**
- **ALL Notification APIs are missing from Swagger**
- **ALL Analytics APIs are missing from Swagger**

## 4. Payment & Subscriptions
- **ALL Payment APIs are missing from Swagger**
- `/api/v1/invoices` (GET)
- `/api/v1/subscription/upgrade` (POST)
