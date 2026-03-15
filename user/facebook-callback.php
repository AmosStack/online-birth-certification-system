<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/dbconnection.php';
require_once __DIR__ . '/google-config.php';

obcs_session_start();

function oauth_fail($slug)
{
    obcs_redirect('login.php?oauth_error=1');
}

function curl_post_form($url, $fields)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode((string)$response, true) ?: array();
}

function curl_get_json($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode((string)$response, true) ?: array();
}

if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['facebook_oauth_state'] = $state;

    $params = http_build_query(array(
        'client_id' => FACEBOOK_APP_ID,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'state' => $state,
        'scope' => 'email,public_profile',
        'response_type' => 'code',
    ));

    obcs_redirect(FACEBOOK_AUTH_URL . '?' . $params);
}

$storedState = isset($_SESSION['facebook_oauth_state']) ? $_SESSION['facebook_oauth_state'] : '';
$returnedState = isset($_GET['state']) ? $_GET['state'] : '';
if ($storedState === '' || !hash_equals($storedState, $returnedState)) {
    oauth_fail('invalid_state');
}
unset($_SESSION['facebook_oauth_state']);

if (isset($_GET['error'])) {
    oauth_fail('access_denied');
}

$tokenData = curl_post_form(FACEBOOK_TOKEN_URL, array(
    'client_id' => FACEBOOK_APP_ID,
    'client_secret' => FACEBOOK_APP_SECRET,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'code' => $_GET['code'],
));

if (empty($tokenData['access_token'])) {
    oauth_fail('token_failed');
}

$facebookUser = curl_get_json(FACEBOOK_USERINFO_URL . '&access_token=' . urlencode($tokenData['access_token']));
if (empty($facebookUser['id'])) {
    oauth_fail('userinfo_failed');
}

$facebookId = $facebookUser['id'];
$email = isset($facebookUser['email']) ? $facebookUser['email'] : '';
$firstName = isset($facebookUser['first_name']) ? $facebookUser['first_name'] : 'User';
$lastName = isset($facebookUser['last_name']) ? $facebookUser['last_name'] : '';

$stmt = $dbh->prepare('SELECT ID FROM tbluser WHERE FacebookID = :facebookid LIMIT 1');
$stmt->bindParam(':facebookid', $facebookId, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_OBJ);

if ($user) {
    obcs_login_user((int) $user->ID, $email !== '' ? $email : $facebookId);
    obcs_redirect('dashboard.php');
}

if ($email !== '') {
    $stmt = $dbh->prepare('SELECT ID FROM tbluser WHERE Email = :email LIMIT 1');
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user) {
        $upd = $dbh->prepare('UPDATE tbluser SET FacebookID = :facebookid WHERE ID = :id');
        $upd->bindParam(':facebookid', $facebookId, PDO::PARAM_STR);
        $upd->bindParam(':id', $user->ID, PDO::PARAM_INT);
        $upd->execute();

        obcs_login_user((int) $user->ID, $email !== '' ? $email : $facebookId);
        obcs_redirect('dashboard.php');
    }
}

$unusablePassword = obcs_hash_password(bin2hex(random_bytes(32)));

$ins = $dbh->prepare('INSERT INTO tbluser (FirstName, LastName, Email, FacebookID, Password) VALUES (:fname, :lname, :email, :facebookid, :password)');
$ins->bindParam(':fname', $firstName, PDO::PARAM_STR);
$ins->bindParam(':lname', $lastName, PDO::PARAM_STR);
$ins->bindParam(':email', $email, PDO::PARAM_STR);
$ins->bindParam(':facebookid', $facebookId, PDO::PARAM_STR);
$ins->bindParam(':password', $unusablePassword, PDO::PARAM_STR);
$ins->execute();

$newId = (int)$dbh->lastInsertId();
if (!$newId) {
    oauth_fail('registration_failed');
}

obcs_login_user($newId, $email !== '' ? $email : $facebookId);
obcs_redirect('dashboard.php');