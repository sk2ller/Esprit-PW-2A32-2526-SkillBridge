<?php
class JWTMiddleware
{
    // ── GET TOKEN FROM REQUEST ────────────────────────────────────────
    public static function getToken()
    {
        // Check for token in Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        // Check for token in cookie
        if (isset($_COOKIE['jwt'])) {
            return $_COOKIE['jwt'];
        }

        // Check for token in session
        if (isset($_SESSION['jwt'])) {
            return $_SESSION['jwt'];
        }

        return null;
    }

    // ── VALIDATE TOKEN AND RETURN USER DATA ────────────────────────────
    public static function authenticate()
    {
        $token = self::getToken();

        if (!$token) {
            return false;
        }

        $payload = JWT::validateToken($token);

        if (!$payload) {
            return false;
        }

        return $payload;
    }

    // ── REQUIRE AUTHENTICATION ─────────────────────────────────────────
    public static function requireLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?action=login');
            exit;
        }

        // Validate JWT token if available
        $payload = self::authenticate();
        if (!$payload) {
            // Token invalid, clear session
            session_destroy();
            header('Location: ?action=login');
            exit;
        }
    }

    // ── REQUIRE ADMIN ──────────────────────────────────────────────────
    public static function requireAdmin()
    {
        self::requireLogin();

        if ($_SESSION['user_role'] != 1) {
            header('HTTP/1.1 403 Forbidden');
            die('Accès refusé.');
        }
    }
}
