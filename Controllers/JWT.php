<?php
class JWT
{
    private static $secret = 'skillbridge-super-secret-key-change-in-production';
    private static $algorithm = 'HS256';
    private static $tokenExpiry = 86400; // 24 hours

    // ── GENERATE TOKEN ────────────────────────────────────────────────
    public static function generateToken($userId, $email, $role)
    {
        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ];

        $payload = [
            'iat' => time(),
            'exp' => time() + self::$tokenExpiry,
            'user_id' => $userId,
            'email' => $email,
            'role' => $role
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = self::sign($headerEncoded . '.' . $payloadEncoded);
        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    // ── VALIDATE TOKEN ────────────────────────────────────────────────
    public static function validateToken($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verify signature
        $signature = self::sign($headerEncoded . '.' . $payloadEncoded);
        $signatureEncoded2 = self::base64UrlEncode($signature);

        if ($signatureEncoded !== $signatureEncoded2) {
            return false;
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // Check expiration
        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    // ── SIGN ──────────────────────────────────────────────────────────
    private static function sign($message)
    {
        return hash_hmac('sha256', $message, self::$secret, true);
    }

    // ── BASE64 URL ENCODE ─────────────────────────────────────────────
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // ── BASE64 URL DECODE ─────────────────────────────────────────────
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', strlen($data) % 4));
    }
}
