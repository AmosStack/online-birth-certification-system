<?php 
// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','obcsdb');
// Establish database connection.
try
{
$dbh = new PDO(
	"mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
	DB_USER,
	DB_PASS,
	array(
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
		PDO::ATTR_EMULATE_PREPARES => false,
	)
);
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}

if (!function_exists('obcs_escape')) {
function obcs_escape($value)
{
return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('obcs_hash_password')) {
function obcs_hash_password($password)
{
return password_hash($password, PASSWORD_DEFAULT);
}
}

if (!function_exists('obcs_is_legacy_md5_hash')) {
function obcs_is_legacy_md5_hash($hash)
{
return is_string($hash) && preg_match('/^[a-f0-9]{32}$/i', $hash) === 1;
}
}

if (!function_exists('obcs_verify_password')) {
function obcs_verify_password($plainPassword, $storedHash)
{
if (!is_string($storedHash) || $storedHash === '') {
return false;
}

if (password_verify($plainPassword, $storedHash)) {
return true;
}

return obcs_is_legacy_md5_hash($storedHash) && hash_equals(strtolower($storedHash), md5($plainPassword));
}
}

if (!function_exists('obcs_password_needs_upgrade')) {
function obcs_password_needs_upgrade($storedHash)
{
if (!is_string($storedHash) || $storedHash === '') {
return true;
}

if (obcs_is_legacy_md5_hash($storedHash)) {
return true;
}

return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
}
}
?>