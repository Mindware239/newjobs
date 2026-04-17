# API Audit Report - Mindware Infotech Job Portal

## 1. Overview
This report provides a comprehensive list of all API endpoints found in the project, categorized by module.

## 2. API Endpoint List (v1)

### Authentication
- `POST /api/v1/login` - User login (Email/Password)
- `POST /api/v1/send-phone-otp` - Send OTP for mobile login
- `POST /api/v1/login-phone` - Login with mobile OTP
- `POST /api/v1/register-candidate` - Register a new candidate
- `POST /api/v1/register-candidate-phone` - Register candidate with mobile OTP
- `POST /api/v1/register-employer` - Register a new employer
- `POST /api/v1/register-employer-phone` - Register employer with mobile OTP
- `POST /api/v1/verify-email` - Verify email address
- `POST /api/v1/verify-otp` - Verify OTP
- `POST /api/v1/resend-otp` - Resend OTP
- `POST /api/v1/forgot-password` - Trigger forgot password flow
- `POST /api/v1/reset-password` - Reset password with token
- `POST /api/v1/auth/google/callback` - Google OAuth callback
- `POST /api/v1/auth/apple/callback` - Apple OAuth callback
- `POST /api/v1/logout` [AUTH] - Logout user
- `POST /api/v1/refresh-token` [AUTH] - Refresh JWT token
- `POST /api/v1/change-password` [AUTH] - Change user password
- `GET /api/v1/me` [AUTH] - Get current user profile info

### Public Jobs
- `GET /api/v1/jobs` - List jobs with keyword/location filters
- `GET /api/v1/jobs/{slug}` - Get job details by slug
- `GET /api/v1/jobs/search` - Search jobs with advanced filters
- `GET /api/v1/jobs/search/filters` - Get available filters for job search

### Utility & Shared
- `GET /api/v1/locations` - List locations
- `GET /api/v1/job-titles` - List job titles
- `GET /api/v1/skills` - List skills
- `GET /api/v1/companies` - List companies
- `GET /api/v1/subscription-plans` - List available plans
- `GET /api/v1/app-version` - Get current app version
- `GET /api/v1/maintenance-status` - Get system maintenance status
- `GET /api/v1/profile` [AUTH] - Get current profile
- `PUT /api/v1/profile` [AUTH] - Update profile
- `POST /api/v1/profile/avatar` [AUTH] - Upload profile avatar
- `DELETE /api/v1/profile/avatar` [AUTH] - Delete profile avatar

### Chat [AUTH]
- `GET /api/v1/conversations` - List user conversations
- `POST /api/v1/conversations` - Create a new conversation
- `GET /api/v1/conversations/{id}/messages` - Get messages for a conversation
- `POST /api/v1/conversations/{id}/messages` - Send a message
- `DELETE /api/v1/conversations/{id}/messages/{msg_id}` - Delete a message
- `PATCH /api/v1/conversations/{id}/messages/{msg_id}` - Edit a message
- `POST /api/v1/conversations/{id}/read` - Mark conversation as read
- `DELETE /api/v1/conversations/{id}` - Delete conversation
- `POST /api/v1/conversations/{id}/block` - Block user
- `POST /api/v1/conversations/{id}/archive` - Archive conversation
- `GET /api/v1/conversations/unread-count` - Get total unread messages count

### Interviews [AUTH]
- `POST /api/v1/interviews/schedule` - Schedule an interview
- `GET /api/v1/interviews` - List user's interviews
- `GET /api/v1/interviews/{id}` - Get interview details
- `PUT /api/v1/interviews/{id}` - Update interview details
- `DELETE /api/v1/interviews/{id}` - Cancel an interview
- `POST /api/v1/interviews/{id}/reschedule` - Reschedule interview
- `POST /api/v1/interviews/{id}/complete` - Mark interview as completed
- `POST /api/v1/interviews/{id}/feedback` - Add interview feedback
- `GET /api/v1/interviews/{id}/jitsi-token` - Get Jitsi video conference token
- `POST /api/v1/interviews/{id}/attendance` - Mark candidate attendance

### Notifications [AUTH]
- `GET /api/v1/notifications` - List notifications
- `POST /api/v1/notifications/{id}/read` - Mark notification as read
- `POST /api/v1/notifications/read-all` - Mark all notifications as read
- `POST /api/v1/notifications/fcm-token` - Register FCM token for push notifications
- `DELETE /api/v1/notifications/fcm-token` - Unregister FCM token
- `GET /api/v1/notifications/preferences` - Get notification settings
- `PUT /api/v1/notifications/preferences` - Update notification settings
- `POST /api/v1/notifications/test` - Send test notification
- `GET /api/v1/notifications/history` - Get notification history

### Reviews [AUTH]
- `GET /api/v1/reviews/company/{id}` - List reviews for a company
- `POST /api/v1/reviews` - Post a new review
- `GET /api/v1/reviews/my-reviews` - Get current user's reviews
- `PUT /api/v1/reviews/{id}` - Update a review
- `DELETE /api/v1/reviews/{id}` - Delete a review
- `GET /api/v1/reviews/company/{id}/stats` - Get review statistics for a company

### Analytics [AUTH]
- `GET /api/v1/analytics/dashboard` - Get overall analytics dashboard data
- `GET /api/v1/analytics/profile-views` - Get profile view stats
- `GET /api/v1/analytics/job/{id}/stats` - Get statistics for a specific job
- `POST /api/v1/analytics/event` - Track custom event

### Payments [AUTH]
- `POST /api/v1/payments/initiate` - Initiate a payment transaction
- `POST /api/v1/payments/verify` - Verify payment success (Razorpay/Cashfree)
- `GET /api/v1/payments/status` - Get payment status
- `GET /api/v1/payments/history` - List payment history
- `POST /api/v1/subscription/upgrade` - Upgrade subscription plan
- `POST /api/v1/subscription/cancel` - Cancel subscription
- `GET /api/v1/subscription/current` - Get current subscription details
- `POST /api/v1/payments/refund` - Request a refund
- `GET /api/v1/invoices` - List invoices
- `GET /api/v1/invoices/{id}/download` - Download invoice PDF
- `POST /api/v1/payments/wallet/add` - Add funds to wallet
- `GET /api/v1/payments/wallet/balance` - Get wallet balance

### Candidate Panel [AUTH/CANDIDATE]
- `GET /api/v1/candidate/profile/detailed` - Get detailed candidate profile
- `POST /api/v1/candidate/profile/education` - Add education
- `PUT /api/v1/candidate/profile/education/{id}` - Update education
- `DELETE /api/v1/candidate/profile/education/{id}` - Delete education
- `POST /api/v1/candidate/profile/experience` - Add work experience
- `PUT /api/v1/candidate/profile/experience/{id}` - Update work experience
- `DELETE /api/v1/candidate/profile/experience/{id}` - Delete work experience
- `POST /api/v1/candidate/profile/skills` - Add skills
- `DELETE /api/v1/candidate/profile/skills/{id}` - Remove skill
- `POST /api/v1/candidate/profile/languages` - Add language
- `DELETE /api/v1/candidate/profile/languages/{id}` - Remove language
- `POST /api/v1/candidate/profile/interests` - Set candidate interests
- `GET /api/v1/candidate/profile/completion-status` - Get profile completeness %
- `POST /api/v1/candidate/resumes/upload` - Upload resume file
- `GET /api/v1/candidate/resumes` - List candidate resumes
- `GET /api/v1/candidate/resumes/{id}` - Get resume details
- `PUT /api/v1/candidate/resumes/{id}` - Update resume metadata
- `DELETE /api/v1/candidate/resumes/{id}` - Delete resume
- `POST /api/v1/candidate/resumes/{id}/parse` - Parse resume content
- `POST /api/v1/candidate/resumes/{id}/download` - Download resume
- `GET /api/v1/candidate/resumes/{id}/preview` - Get resume preview
- `POST /api/v1/candidate/resumes/create-from-profile` - Generate resume from profile
- `PUT /api/v1/candidate/resumes/{id}/set-default` - Set default resume
- `GET /api/v1/candidate/resume-templates` - List resume templates
- `POST /api/v1/candidate/resumes/{id}/export` - Export resume to PDF
- `GET /api/v1/candidate/applications` - List job applications
- `GET /api/v1/candidate/applications/{id}` - Get application details
- `POST /api/v1/candidate/applications/{id}/withdraw` - Withdraw application
- `POST /api/v1/candidate/applications/{id}/accept-offer` - Accept job offer
- `POST /api/v1/candidate/applications/{id}/reject-offer` - Reject job offer
- `POST /api/v1/candidate/jobs/{id}/apply` - Apply to a job
- `POST /api/v1/candidate/jobs/{id}/bookmark` - Bookmark a job
- `DELETE /api/v1/candidate/jobs/{id}/bookmark` - Unbookmark a job
- `GET /api/v1/candidate/bookmarks` - List bookmarks
- `POST /api/v1/candidate/bookmarks/bulk-delete` - Bulk delete bookmarks
- `POST /api/v1/candidate/jobs/search-saved` - Save a search query
- `GET /api/v1/candidate/saved-searches` - List saved searches
- `DELETE /api/v1/candidate/saved-searches/{id}` - Delete saved search
- `GET /api/v1/candidate/jobs/trending` - List trending jobs
- `POST /api/v1/candidate/job-alerts` - Create job alert
- `GET /api/v1/candidate/job-alerts` - List job alerts
- `PUT /api/v1/candidate/job-alerts/{id}` - Update job alert
- `DELETE /api/v1/candidate/job-alerts/{id}` - Delete job alert
- `GET /api/v1/candidate/job-alerts/{id}/count` - Get matching count for alert
- `GET /api/v1/candidate/analytics/profile-views` - Get profile view stats
- `GET /api/v1/candidate/analytics/applications` - Get application stats
- `GET /api/v1/candidate/dashboard` - Get candidate dashboard overview

### Employer Panel [AUTH/EMPLOYER]
- `GET /api/v1/employer/profile` - Get company profile
- `PUT /api/v1/employer/profile` - Update company profile
- `POST /api/v1/employer/profile/logo` - Upload company logo
- `POST /api/v1/employer/profile/banner` - Upload company banner
- `POST /api/v1/employer/profile/documents/upload` - Upload verification documents
- `GET /api/v1/employer/profile/documents` - List uploaded documents
- `DELETE /api/v1/employer/profile/documents/{id}` - Delete a document
- `GET /api/v1/employer/profile/verification-status` - Get verification status
- `POST /api/v1/employer/profile/social-links` - Update social links
- `GET /api/v1/employer/jobs` - List posted jobs
- `POST /api/v1/employer/jobs` - Create a new job
- `PUT /api/v1/employer/jobs/{id}` - Update a job
- `DELETE /api/v1/employer/jobs/{id}` - Delete a job
- `GET /api/v1/employer/jobs/{id}/details` - Get job details
- `POST /api/v1/employer/jobs/{id}/duplicate` - Duplicate a job
- `POST /api/v1/employer/jobs/{id}/publish` - Publish a job
- `POST /api/v1/employer/jobs/{id}/unpublish` - Unpublish a job
- `GET /api/v1/employer/applications` - List received applications
- `GET /api/v1/employer/applications/{id}` - Get application details
- `GET /api/v1/employer/applications/{id}/resume` - Download candidate resume
- `POST /api/v1/employer/applications/{id}/shortlist` - Shortlist a candidate
- `POST /api/v1/employer/applications/{id}/reject` - Reject an application
- `POST /api/v1/employer/applications/{id}/send-offer` - Send job offer to candidate
- `GET /api/v1/employer/candidates` - Search/Browse candidates
- `GET /api/v1/employer/candidates/{id}` - Get candidate details
- `POST /api/v1/employer/candidates/{id}/invite` - Invite candidate to apply
- `POST /api/v1/employer/candidates/{id}/shortlist` - Shortlist candidate for future
- `GET /api/v1/employer/shortlists` - List shortlisted candidates
- `GET /api/v1/employer/team-members` - List team members
- `POST /api/v1/employer/team-members` - Add team member
- `PUT /api/v1/employer/team-members/{id}` - Update team member
- `DELETE /api/v1/employer/team-members/{id}` - Remove team member
- `GET /api/v1/employer/dashboard` - Get employer dashboard overview
- `GET /api/v1/employer/dashboard/stats` - Get quick stats
- `GET /api/v1/employer/dashboard/recent-applications` - Get recent applications
- `GET /api/v1/employer/dashboard/active-jobs` - Get active jobs
- `GET /api/v1/employer/dashboard/upcoming-interviews` - Get upcoming interviews
- `GET /api/v1/employer/analytics/job-stats` - Get job-wise statistics
- `GET /api/v1/employer/analytics/candidates-views` - Get candidate profile view stats

---

## 3. APIs found in `routes/api.php` (Core/Legacy)
- `GET /api/fcm-web-config`
- `POST /api/push/register`
- `GET /api/qualifications/suggest`
- `POST /api/push/unsubscribe`
- `POST /api/push/test`
- `POST /api/notifications/preferences`
- `POST /api/discount-code/validate`
- `GET /api/job-titles/search`
- `GET /api/locations/search`
- `GET /api/locations/all`
- `GET /api/industries/all`
- `POST /api/location/detect`
- `GET /api/countries`
- `GET /api/states`
- `GET /api/cities`
