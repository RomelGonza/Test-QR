<?php
// Database params — read from environment or use defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ubvwmzhw_onta');

// App Root
define('APPROOT', dirname(dirname(__FILE__)) . '/app');
// URL Root — prefer APP_URL, otherwise auto-detect host/scheme so assets resolve even if APP_ENV is unset
$appUrlEnv = getenv('APP_URL');
$appEnv = getenv('APP_ENV') ?: '';
// Fallback defaults to /onta/ for legacy dev setups. Override via APP_FALLBACK_PATH when needed.
$rawFallback = getenv('APP_FALLBACK_PATH');
if (!empty($rawFallback) && preg_match('/^[\\/a-zA-Z0-9_-]+$/', $rawFallback)) {
    $fallbackPath = $rawFallback;
} else {
    $fallbackPath = '/onta/';
}
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$httpsFlag = $_SERVER['HTTPS'] ?? '';
$forceHttps = filter_var(getenv('APP_FORCE_HTTPS'), FILTER_VALIDATE_BOOLEAN);

// Trust HTTPS only when the proxy explicitly declares it or the server marks HTTPS.
$isDirectHttps = !empty($httpsFlag) && $httpsFlag !== 'off';
$isTrustedProxy = $appEnv === 'production' && $forwardedProto === 'https';
if ($isDirectHttps || $isTrustedProxy || $forceHttps) {
    $scheme = 'https';
} else {
    $scheme = 'http';
}

// Validate the host to prevent header injection (HTTP_HOST/SERVER_NAME are client-controlled)
$rawHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$host = '';
if (!empty($rawHost)) {
    $hostParts = explode(':', $rawHost, 2);
    $domain = $hostParts[0];
    $port = $hostParts[1] ?? '';

    if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        $host = $domain;
        if ($port !== '' && ctype_digit($port)) {
            $portNum = (int) $port;
            if ($portNum >= 1 && $portNum <= 65535) {
                $host .= ':' . $portNum;
            }
        }
    }
}

if (!empty($appUrlEnv)) {
    // Use explicit APP_URL if provided
    $urlroot = $appUrlEnv;
} elseif (!empty($host)) {
    // Derive from current request host (works for Railway/NGINX/Apache)
    $urlroot = $scheme . '://' . $host . '/';
} else {
    // CLI or environments without HTTP context fall back to local dev path
    $normalizedFallback = '/' . trim($fallbackPath, '/') . '/';
    $urlroot = $scheme . '://localhost' . $normalizedFallback;
}
// Ensure URLROOT always ends with /
define('URLROOT', rtrim($urlroot, '/') . '/');
// Site Name
define('SITENAME', getenv('SITENAME') ?: 'ONTA PERU 2026');
// App Version
define('APPVERSION', '1.0.0');
// Debug Mode — true only if APP_ENV != production
define('DEBUG', getenv('APP_ENV') !== 'production');

// Google reCAPTCHA Keys (set via environment variables in production)
define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
?>
