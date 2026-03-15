<?php
/**
 * Google OAuth 2.0 Configuration
 *
 * Steps to get your credentials:
 *  1. Go to https://console.cloud.google.com/
 *  2. Create a project (or select an existing one).
 *  3. Navigate to APIs & Services > Credentials.
 *  4. Click "Create Credentials" > "OAuth client ID".
 *  5. Application type: Web application.
 *  6. Add the URI below under "Authorised redirect URIs".
 *  7. Copy the Client ID and Client Secret here.
 *  8. Also enable the "Google People API" under APIs & Services > Library.
 */

define('GOOGLE_CLIENT_ID',     '151908708748-03nnrg734l6m071apqui5sl88p51sme0.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

// Use a fixed base URL so Google always receives one deterministic redirect URI.
// This value must match how you open the project in the browser.
define('OBCS_APP_URL', 'http://localhost/online-birth-certification-system');

// Must exactly match an Authorised Redirect URI in your Google Console project.
define('GOOGLE_REDIRECT_URI',  rtrim(OBCS_APP_URL, '/') . '/user/google-callback.php');

// Facebook OAuth settings.
define('FACEBOOK_APP_ID',     'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET');
define('FACEBOOK_REDIRECT_URI', rtrim(OBCS_APP_URL, '/') . '/user/facebook-callback.php');

// Google OAuth endpoints
define('GOOGLE_AUTH_URL',     'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL',    'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v3/userinfo');

// Facebook OAuth endpoints
define('FACEBOOK_AUTH_URL',      'https://www.facebook.com/v19.0/dialog/oauth');
define('FACEBOOK_TOKEN_URL',     'https://graph.facebook.com/v19.0/oauth/access_token');
define('FACEBOOK_USERINFO_URL',  'https://graph.facebook.com/me?fields=id,first_name,last_name,email');
