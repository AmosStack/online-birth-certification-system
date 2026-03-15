<?php
declare(strict_types=1);

if (!defined('OBCS_BOOTSTRAPPED')) {
    define('OBCS_BOOTSTRAPPED', true);

    error_reporting(E_ALL);
    ini_set('display_errors', '0');

    defined('DB_HOST') || define('DB_HOST', 'localhost');
    defined('DB_USER') || define('DB_USER', 'root');
    defined('DB_PASS') || define('DB_PASS', '');
    defined('DB_NAME') || define('DB_NAME', 'obcsdb');
}

if (!function_exists('obcs_session_start')) {
    function obcs_session_start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

if (!function_exists('obcs_db')) {
    function obcs_db(): PDO
    {
        static $dbh = null;

        if ($dbh instanceof PDO) {
            return $dbh;
        }

        try {
            $dbh = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                ]
            );
        } catch (PDOException $exception) {
            http_response_code(500);
            exit('Database connection failed.');
        }

        return $dbh;
    }
}

if (!function_exists('obcs_escape')) {
    function obcs_escape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('obcs_input_string')) {
    function obcs_input_string(array $source, string $key): string
    {
        return trim((string) ($source[$key] ?? ''));
    }
}

if (!function_exists('obcs_hash_password')) {
    function obcs_hash_password(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('obcs_is_legacy_md5_hash')) {
    function obcs_is_legacy_md5_hash($hash): bool
    {
        return is_string($hash) && preg_match('/^[a-f0-9]{32}$/i', $hash) === 1;
    }
}

if (!function_exists('obcs_verify_password')) {
    function obcs_verify_password(string $plainPassword, $storedHash): bool
    {
        if (!is_string($storedHash) || $storedHash === '') {
            return false;
        }

        if (password_verify($plainPassword, $storedHash)) {
            return true;
        }

        return obcs_is_legacy_md5_hash($storedHash)
            && hash_equals(strtolower($storedHash), md5($plainPassword));
    }
}

if (!function_exists('obcs_password_needs_upgrade')) {
    function obcs_password_needs_upgrade($storedHash): bool
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

if (!function_exists('obcs_redirect')) {
    function obcs_redirect(string $path): void
    {
        header('Location: ' . $path);
        exit();
    }
}

if (!function_exists('obcs_login_user')) {
    function obcs_login_user(int $userId, string $loginIdentifier): void
    {
        obcs_session_start();
        session_regenerate_id(true);
        $_SESSION['obcsuid'] = $userId;
        $_SESSION['login'] = $loginIdentifier;
    }
}

if (!function_exists('obcs_login_admin')) {
    function obcs_login_admin(int $adminId, string $loginIdentifier): void
    {
        obcs_session_start();
        session_regenerate_id(true);
        $_SESSION['obcsaid'] = $adminId;
        $_SESSION['login'] = $loginIdentifier;
    }
}

if (!function_exists('obcs_require_user')) {
    function obcs_require_user(): void
    {
        obcs_session_start();

        if (empty($_SESSION['obcsuid'])) {
            obcs_redirect('logout.php');
        }
    }
}

if (!function_exists('obcs_require_admin')) {
    function obcs_require_admin(): void
    {
        obcs_session_start();

        if (empty($_SESSION['obcsaid'])) {
            obcs_redirect('logout.php');
        }
    }
}

if (!function_exists('obcs_logout_to')) {
    function obcs_logout_to(string $path): void
    {
        obcs_session_start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
        obcs_redirect($path);
    }
}