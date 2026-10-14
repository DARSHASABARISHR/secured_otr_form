<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('secured_otr_session');
    session_start();
}
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function db(): PDO
{
    static $p = null;
    if ($p instanceof PDO) return $p;
    $p = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    return $p;
}
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function json_response(array $a, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($a);
    exit;
}
function check_csrf(): void
{
    if (!hash_equals((string)$_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) json_response(['ok' => false, 'message' => 'Session expired. Refresh and try again.'], 419);
}
function valid_email(string $s): bool
{
    return filter_var($s, FILTER_VALIDATE_EMAIL) !== false;
}
function valid_phone(string $s): bool
{
    return (bool)preg_match('/^[6-9][0-9]{9}$/', $s);
}
function otp_code(): string
{
    return str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
}
function now_sql(): string
{
    return date('Y-m-d H:i:s');
}
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function valid_password_rules(string $s): bool
{
    return strlen($s) >= 8 && preg_match('/[A-Z]/', $s) === 1 && preg_match('/[a-z]/', $s) === 1 && preg_match('/[^A-Za-z0-9]/', $s) === 1;
}
