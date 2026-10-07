<?php

/*
 * Facebook and LinkedIn login (Google is in config/google.php). A provider's button is shown only when
 * both keys are set – never show a button that is not wired (fake buttons got jobsence.com flagged as
 * "deceptive" by Google Safe Browsing in Oct 2026).
 *
 * Facebook: developers.facebook.com → Create app → "Authenticate and request data from users with
 *   Facebook Login" → Facebook Login → Settings → Valid OAuth Redirect URIs:
 *     https://jobsence.com/auth/facebook/callback, https://www.jobsence.com/auth/facebook/callback
 *   (localhost redirects are allowed automatically while the app is in Development mode).
 *   Permissions: email, public_profile. App settings → Basic: App ID + App Secret, privacy policy URL
 *   https://jobsence.com/privacy (also give it as the data-deletion instructions URL).
 * LinkedIn: linkedin.com/developers → Create app (company page: Jobsence) → Products → add
 *   "Sign In with LinkedIn using OpenID Connect" → Auth → Authorized redirect URLs:
 *     https://jobsence.com/auth/linkedin/callback, https://www.jobsence.com/auth/linkedin/callback,
 *     http://localhost:8081/auth/linkedin/callback
 *   Client ID + Primary Client Secret.
 */
return [
    'facebook' => [
        'client_id' => $_ENV['FACEBOOK_APP_ID'] ?? '',
        'client_secret' => $_ENV['FACEBOOK_APP_SECRET'] ?? '',
        'graph' => 'v19.0',
    ],
    'linkedin' => [
        'client_id' => $_ENV['LINKEDIN_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['LINKEDIN_CLIENT_SECRET'] ?? '',
    ],
];
