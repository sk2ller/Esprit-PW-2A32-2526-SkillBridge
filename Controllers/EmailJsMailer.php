<?php
require_once __DIR__ . '/../config.php';

class EmailJsMailer
{
    public static function sendSecurityCode($toEmail, $toName, $code, $purpose)
    {
        $isTwoFactor = $purpose === '2fa';
        $title = $isTwoFactor ? 'Your SkillBridge login code' : 'Verify your SkillBridge email';
        $message = $purpose === '2fa'
            ? 'Use this code to finish signing in to your SkillBridge account.'
            : 'Use this code to verify your email address and activate your SkillBridge account.';
        $actionText = $isTwoFactor ? 'Complete login' : 'Verify email';
        $purposeLabel = $isTwoFactor ? 'Two-factor authentication' : 'Email verification';

        return self::send([
            'to_email' => $toEmail,
            'to_name' => $toName,
            'user_email' => $toEmail,
            'user_name' => $toName,
            'subject' => $title,
            'title' => $title,
            'mail_title' => $title,
            'heading' => $title,
            'message' => $message,
            'mail_message' => $message,
            'intro' => $message,
            'security_code' => $code,
            'code' => $code,
            'verification_code' => $code,
            'two_factor_code' => $code,
            'otp_code' => $code,
            'temporary_password' => $code,
            'password' => $code,
            'code_label' => $purposeLabel . ' code',
            'temporary_password_label' => $purposeLabel . ' code',
            'action_text' => $actionText,
            'login_url' => 'http://localhost:8000/?action=login',
            'verify_url' => 'http://localhost:8000/?action=verify_email&email=' . rawurlencode($toEmail),
            'app_name' => 'SkillBridge'
        ], true);
    }

    public static function send($templateParams, $preferSecurityTemplate = false)
    {
        $config = Config::getEmailJsConfig();
        if (!self::looksConfigured($config)) {
            return [
                'success' => false,
                'message' => 'EmailJS is not configured. Add service id, template id and public key in config.php.'
            ];
        }

        $templateId = $preferSecurityTemplate && !empty($config['security_template_id'])
            ? $config['security_template_id']
            : $config['template_id'];

        $payload = [
            'service_id' => $config['service_id'],
            'template_id' => $templateId,
            'user_id' => $config['public_key'],
            'template_params' => $templateParams
        ];

        if (!empty($config['access_token'])) {
            $payload['accessToken'] = $config['access_token'];
        }

        $ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 25
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'message' => 'EmailJS request failed: ' . $curlError];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['success' => false, 'message' => 'EmailJS error: ' . trim((string) $response)];
        }

        return ['success' => true];
    }

    private static function looksConfigured($config)
    {
        return !empty($config['service_id'])
            && !empty($config['template_id'])
            && !empty($config['public_key'])
            && strpos($config['service_id'], 'YOUR_') !== 0
            && strpos($config['template_id'], 'YOUR_') !== 0
            && strpos($config['public_key'], 'YOUR_') !== 0;
    }
}
