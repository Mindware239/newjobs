# Jobsence - Job Portal Project Overview

## 📁 Project Structure

### Core Architecture
- **Framework**: Custom PHP MVC Framework (PHP 8.2+)
- **Entry Point**: `public/index.php`
- **Autoloading**: PSR-4 (Composer)
- **Database**: MySQL/MariaDB (PDO)
- **Cache**: Redis
- **Search**: Elasticsearch (optional)

### 📂 Directory Structure

```
mindinfotech/
├── app/
│   ├── Controllers/          # Request handlers (MVC - Controller)
│   ├── Models/              # Database models (MVC - Model)
│   ├── Core/                # Framework core classes
│   ├── Middlewares/         # HTTP middleware
│   ├── Services/            # Business logic services
│   ├── Helpers/             # Utility functions
│   └── Workers/             # Background job workers
├── config/                  # Configuration files
├── database/                # Database migrations & scripts
├── public/                  # Public web root
├── resources/
│   ├── views/              # Template files (MVC - View)
│   └── emails/             # Email templates
├── routes/                  # Route definitions
├── storage/                 # Logs, uploads, cache
└── vendor/                  # Composer dependencies
```

---

## 🎯 User Roles & Modules

### 1. **Public/Front** (No Login Required)
- Homepage with job listings
- Job search & browsing (`/jobs`)
- Job detail pages (`/job/{slug}`)
- Company profiles (`/company/{slug}`)
- Blog pages
- About, Contact pages

### 2. **Candidate** (`/candidate/*`)
- Dashboard
- Job browsing & applications
- Profile management
- Saved jobs
- Chat with employers
- Premium subscriptions
- Notifications
- Interviews

### 3. **Employer** (`/employer/*`)
- Dashboard
- Job posting & management
- Company profile management
- Application management
- Candidate matching
- Interviews
- Analytics
- Subscription & billing
- KYC verification
- Messages/Chat

### 4. **Admin** (`/admin/*`)
- Dashboard
- Job & employer management
- Candidate management
- Category & tag management
- Blog management
- Payment management
- Reports
- Settings

### 5. **Master Admin** (`/masteradmin/*`)
- System settings
- Role & permission management
- Sales management
- Verification management
- Support tickets
- System logs

### 6. **Sales Team** (`/sales/*`)
- Lead management
- Pipeline management
- Campaigns
- Analytics
- Follow-ups
- Team management

### 7. **Finance Manager** (`/finance/*`)
- Payment management
- Financial reports

### 8. **Support Executive** (`/support/*`)
- Support ticket management

### 9. **Verification Executive** (`/verification/*`)
- KYC verification management

---

## 🗄️ Database Models (64 Models)

### Core Models
- `User` - Base user accounts
- `Candidate` - Candidate profiles
- `Employer` - Employer/company accounts
- `Job` - Job postings
- `Application` - Job applications
- `Company` - Company profiles
- `CompanyBlog` - Company blog posts
- `CompanyFollower` - Company follow relationships
- `Review` - Company reviews

### Job-Related
- `JobLocation`, `JobSkill`, `JobBenefit`, `JobBookmark`, `JobView`
- `Skill`, `Benefit`, `Category`

### Application & Interview
- `Application`, `ApplicationEvent`, `Interview`

### Communication
- `Conversation`, `Message`, `Notification`

### Subscription & Payment
- `SubscriptionPlan`, `EmployerSubscription`, `SubscriptionPayment`
- `CandidatePremiumPurchase`, `DiscountCode`

### Sales CRM
- `SalesLead`, `SalesUser`, `SalesStage`, `SalesPayment`
- `SalesActivity`, `SalesFollowup`, `SalesCommunication`

### System
- `Role`, `Permission`, `AuditLog`, `SystemSetting`
- `Verification`, `Webhook`

---

## 🛣️ Routing Structure

### Route Files
1. **routes/front.php** - Public routes (home, jobs, blogs, auth)
2. **routes/candidate.php** - Candidate panel routes
3. **routes/employer.php** - Employer panel routes
4. **routes/admin.php** - Admin panel routes
5. **routes/masteradmin.php** - Master admin routes
6. **routes/sales.php** - Sales CRM routes
7. **routes/api.php** - API endpoints

### Key Public Routes
- `/` - Homepage
- `/jobs` - Job listing (public)
- `/job/{slug}` - Job detail (public)
- `/company/{slug}` - Company profile (public)
- `/blog` - Blog listing
- `/login`, `/register-*` - Authentication

---

## 🔧 Core Classes

### Core Framework (`app/Core/`)
- **Application.php** - Main application bootstrap
- **Router.php** - Route matching & dispatching
- **Request.php** - HTTP request handler
- **Response.php** - HTTP response handler
- **Database.php** - PDO database wrapper
- **Storage.php** - File upload/storage manager
- **RedisClient.php** - Redis cache client

### Base Classes
- **BaseController** - Base controller with auth helpers
- **Model** - Base model with ORM-like methods

---

## 🔐 Authentication & Security

### Authentication
- Session-based authentication
- Google OAuth integration
- Apple OAuth (configured)
- Password reset functionality

### Middleware
- `AuthMiddleware` - Authentication check
- `CsrfMiddleware` - CSRF protection
- `RateLimitMiddleware` - Rate limiting
- `AdminMiddleware` - Admin role check
- `RbacMiddleware` - Role-based access control
- `AntiSpamMiddleware` - Spam prevention
- `SubscriptionMiddleware` - Subscription checks

---

## 💳 Payment Integration

### Payment Gateways
- **Razorpay** - Primary payment gateway
- Webhook handling for payment callbacks

### Subscription System
- Subscription plans (monthly/quarterly/annual)
- Employer subscriptions
- Candidate premium purchases
- Usage tracking

---

## 🔍 Search & Matching

### Features
- Job search with filters (keyword, location, industry, type)
- Job matching algorithm (`JobMatchService`)
- Elasticsearch integration (optional)
- Job recommendations for candidates

---

## 📧 Communication

### Features
- Messaging system (employer-candidate chat)
- Email notifications (`MailService`)
- Push notifications
- In-app notifications

---

## 📊 Analytics & Reporting

### Services
- `AnalyticsService` - Analytics data
- Employer analytics dashboard
- Sales analytics
- Admin reports

---

## 🤖 AI/ML Features

### Services
- `AIJobDescriptionService` - AI-generated job descriptions
- `AIResumeParser` - Resume parsing with AI
- `ResumeParserService` - Resume extraction
- `OcrService` - OCR for documents

---

## 🎨 Frontend Technology

### UI Framework
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - JavaScript framework (lightweight Vue-like)
- **Quill.js** - Rich text editor (for job descriptions)

### Assets
- Custom CSS/JS in views
- Image uploads stored in `public/uploads/`
- Company logos, banners, job category images

---

## 📝 Current Status & Issues

### ✅ Recently Fixed
1. Public job listing page (`/jobs`) - No login required
2. Public job detail pages (`/job/{slug}`) - No login required
3. Public company profiles (`/company/{slug}`) - No login required
4. Company profile layout & spacing issues fixed
5. Login required only for actions (apply, bookmark, chat, follow)

### 🔍 Potential Issues to Review

#### 1. **Route Conflicts**
- Public routes might conflict with candidate routes
- Check route priority in `public/index.php`

#### 2. **Authentication Flow**
- Some controllers use `ensureCandidate()` (redirects to login)
- Others use `requireAuth()` (returns JSON error)
- Need consistency

#### 3. **Database Queries**
- Mix of raw SQL and Model methods
- Some models use `Database::fetchOne()`, others use PDO directly
- Need consistency in query patterns

#### 4. **Error Handling**
- Some controllers have try-catch, others don't
- Error logging location: `storage/logs/php_errors.log`
- 404 pages need review

#### 5. **File Structure**
- Some controllers in `company/` namespace (lowercase)
- Should be `Company/` for PSR-4 compliance
- Models use PascalCase correctly

#### 6. **View Organization**
- Mix of layouts (some use `include/header.php`, others don't)
- Need consistent layout system
- Skeleton loading styles added but not fully implemented

#### 7. **Configuration**
- Environment variables via `.env` (not in repo)
- Config files in `config/` directory
- Redis, Elasticsearch optional

---

## 🎯 Key Features by Module

### Job Management
- ✅ Multi-step job posting wizard
- ✅ Job editing & publishing
- ✅ Job categories & industries
- ✅ Job locations (city, state, country)
- ✅ Skills & benefits management
- ✅ Job bookmarking
- ✅ Job views tracking

### Company Profiles
- ✅ Company profile management (employer)
- ✅ Public company profiles
- ✅ Company blogs
- ✅ Company reviews
- ✅ Company followers
- ✅ Company stats (rating, reviews count)

### Applications
- ✅ Application submission
- ✅ Application status tracking
- ✅ Interview scheduling
- ✅ Application events/history

### Communication
- ✅ Employer-candidate chat
- ✅ Email notifications
- ✅ In-app notifications
- ✅ Unread message counts

### Premium Features
- ✅ Candidate premium subscriptions
- ✅ Employer subscriptions
- ✅ Feature gating based on subscription

---

## 🚀 Next Steps Recommendations

1. **Standardize Error Handling**
   - Consistent try-catch blocks
   - Proper error logging
   - User-friendly error pages

2. **Code Consistency**
   - Standardize database query methods
   - Consistent controller naming (PascalCase)
   - Unified authentication checks

3. **Testing**
   - Unit tests for models
   - Integration tests for controllers
   - E2E tests for critical flows

4. **Documentation**
   - API documentation (Swagger exists)
   - Code comments
   - User guides

5. **Performance**
   - Implement caching strategy
   - Database query optimization
   - Asset optimization

6. **Security**
   - Input validation review
   - SQL injection prevention (PDO prepared statements)
   - XSS prevention (htmlspecialchars usage)
   - CSRF protection (middleware exists)

---

## 📦 Dependencies

### PHP Packages (via Composer)
- `vlucas/phpdotenv` - Environment variables
- `elasticsearch/elasticsearch` - Search engine
- `monolog/monolog` - Logging
- `google/apiclient` - Google OAuth
- `firebase/php-jwt` - JWT tokens
- `phpmailer/phpmailer` - Email sending
- `razorpay/razorpay` - Payment gateway
- `dompdf/dompdf` - PDF generation

### Required PHP Extensions
- `ext-pdo` - Database access
- `ext-redis` - Redis cache
- `ext-json` - JSON handling
- `ext-mbstring` - Multibyte strings
- `ext-curl` - HTTP requests

---

## 🎨 UI/UX Features

- Responsive design (Tailwind CSS)
- Dark/light mode support (if implemented)
- Skeleton loading states
- Form validation
- File uploads (images, PDFs, videos)
- Rich text editing (Quill.js)
- Interactive maps (Leaflet.js - if used)

---

This overview provides a comprehensive understanding of the project structure, features, and areas that may need attention.





