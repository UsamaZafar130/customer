<?php
/**
 * HTTPS Redirection and Security Headers
 * This file should be included at the top of every page
 */

// Force HTTPS redirection
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
        $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirectURL", true, 301);
        exit();
    }
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Set CSP header (Content Security Policy)
$csp = "default-src 'self'; " .
       "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
       "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
       "img-src 'self' data: https://admin.frozofun.com https://cdn.jsdelivr.net; " .
       "font-src 'self' https://cdn.jsdelivr.net; " .
       "connect-src 'self'";

header("Content-Security-Policy: $csp");
?>