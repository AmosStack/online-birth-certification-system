<?php
declare(strict_types=1);

/**
 * Google OAuth 2.0 callback handler.
 *
 * Two phases are handled in this single file:
 *   Phase 1 – No ?code in URL  → build the Google authorisation URL and redirect there.
 *   Phase 2 – ?code in URL      → exchange the code for a token, fetch the user's
 *                                   Google profile, then log in (or create) the account.
 */

require_once __DIR__ . '/includes/dbconnection.php';
require_once __DIR__ . '/google-config.php';

obcs_session_start();

// ── Helpers ────────────────────────────────────────────────────────────────────

/**
 * Abort with a redirect to the login page and a brief error slug.
 * The slug is shown as a generic warning – never expose OAuth details to users.
 */
function oauth_fail($slug)
{
    obcs_redirect('login.php?oauth_error=1');
}

/**
 * Make a cURL POST request and return the decoded JSON response.
 */
function curl_post(string $url, array $fields): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        15);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode((string)$response, true) ?? [];
}

/**
 * Make an authenticated cURL GET request and return the decoded JSON response.
 */
function curl_get_authed(string $url, string $bearer_token): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Authorization: Bearer ' . $bearer_token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        15);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode((string)$response, true) ?? [];
}

// ── Phase 1: Redirect to Google ────────────────────────────────────────────────

if (!isset($_GET['code'])) {
    // Generate a cryptographically random CSRF state token.
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;

    $params = http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'access_type'   => 'online',
        'prompt'        => 'select_account',
    ]);

    obcs_redirect(GOOGLE_AUTH_URL . '?' . $params);
}

// ── Phase 2: Handle Google callback ───────────────────────────────────────────

// Validate CSRF state – protects against CSRF attacks.
$storedState = $_SESSION['google_oauth_state'] ?? '';
$returnedState = $_GET['state'] ?? '';
if (!hash_equals($storedState, $returnedState)) {
    oauth_fail('invalid_state');
}
unset($_SESSION['google_oauth_state']);

// User may have denied access on the Google consent screen.
if (isset($_GET['error'])) {
    oauth_fail('access_denied');
}

// ── Exchange authorisation code for an access token ────────────────────────────

$tokenData = curl_post(GOOGLE_TOKEN_URL, [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (empty($tokenData['access_token'])) {
    oauth_fail('token_failed');
}

$accessToken = $tokenData['access_token'];

// ── Fetch user profile from Google ────────────────────────────────────────────

$googleUser = curl_get_authed(GOOGLE_USERINFO_URL, $accessToken);

if (empty($googleUser['sub'])) {
    oauth_fail('userinfo_failed');
}

$googleId  = $googleUser['sub'];
$email     = $googleUser['email']       ?? '';
$firstName = $googleUser['given_name']  ?? 'User';
$lastName  = $googleUser['family_name'] ?? '';

// ── 1. Look up an existing account linked to this Google ID ───────────────────

$stmt = $dbh->prepare("SELECT ID FROM tbluser WHERE GoogleID = :gid LIMIT 1");
$stmt->bindParam(':gid', $googleId, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_OBJ);

if ($user) {
    obcs_login_user((int) $user->ID, $email !== '' ? $email : $googleId);
    obcs_redirect('dashboard.php');
}

// ── 2. Link to an existing account that shares the same email ─────────────────

if (!empty($email)) {
    $stmt = $dbh->prepare("SELECT ID FROM tbluser WHERE Email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user) {
        $upd = $dbh->prepare("UPDATE tbluser SET GoogleID = :gid WHERE ID = :id");
        $upd->bindParam(':gid', $googleId,  PDO::PARAM_STR);
        $upd->bindParam(':id',  $user->ID,  PDO::PARAM_INT);
        $upd->execute();

        obcs_login_user((int) $user->ID, $email !== '' ? $email : $googleId);
        obcs_redirect('dashboard.php');
    }
}

// ── 3. Create a new account ────────────────────────────────────────────────────

// Google-only users never use a password, so we store a random unusable hash.
$unusablePassword = obcs_hash_password(bin2hex(random_bytes(32)));

$ins = $dbh->prepare(
    "INSERT INTO tbluser (FirstName, LastName, Email, GoogleID, Password)
     VALUES (:fname, :lname, :email, :gid, :password)"
);
$ins->bindParam(':fname',    $firstName,       PDO::PARAM_STR);
$ins->bindParam(':lname',    $lastName,        PDO::PARAM_STR);
$ins->bindParam(':email',    $email,           PDO::PARAM_STR);
$ins->bindParam(':gid',      $googleId,        PDO::PARAM_STR);
$ins->bindParam(':password', $unusablePassword, PDO::PARAM_STR);
$ins->execute();

$newId = (int)$dbh->lastInsertId();

if (!$newId) {
    oauth_fail('registration_failed');
}

obcs_login_user($newId, $email !== '' ? $email : $googleId);
obcs_redirect('dashboard.php');
