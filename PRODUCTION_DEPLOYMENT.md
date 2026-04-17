# Production Deployment & Stability Guide

## 1. Environment Configuration (.env)
Ensure your `.env` file is properly configured for production:
```env
APP_ENV=production
APP_DEBUG=false
DISPLAY_ERRORS=0

# Database
DB_HOST=127.0.0.1
DB_NAME=mindware_prod
DB_USER=prod_user
DB_PASS=secure_password

# Redis (Required for Rate Limiting)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# JWT
JWT_SECRET=your_super_secret_key_here
JWT_EXPIRY=3600
```

## 2. Server Configuration (php.ini)
```ini
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/www/html/storage/logs/php_errors.log
memory_limit = 256M
post_max_size = 20M
upload_max_filesize = 20M
```

## 3. Database Indexing Suggestions
For high-traffic search and filtering, ensure these indexes exist:
```sql
-- Jobs Search Optimization
CREATE INDEX idx_jobs_status_category ON jobs(status, category);
CREATE INDEX idx_jobs_employer_id ON jobs(employer_id);

-- Locations Search Optimization
CREATE INDEX idx_cities_name ON cities(name);
CREATE INDEX idx_job_locations_city_id ON job_locations(city_id);

-- Skills Optimization
CREATE INDEX idx_skills_name ON skills(name);
CREATE INDEX idx_job_skills_skill_id ON job_skills(skill_id);

-- Notifications
CREATE INDEX idx_notifications_user_is_read ON notifications(user_id, is_read);
```

## 4. Folder Permissions
```bash
# Storage and Logs must be writable by web server (www-data / apache)
chmod -R 775 storage
chown -R www-data:www-data storage
```

## 5. Security Checklist
- [x] JWT Expiry implemented and verified.
- [x] Global Exception Handler active (no sensitive leaks).
- [x] Input sanitization via `ApiController::sanitize`.
- [x] Prepared statements used for all SQL.
- [x] Rate limiting active on Auth/OTP endpoints.
- [x] Health check monitoring active (`/api/v1/health`).
