<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Helper class for managing SSL/TLS CA certificate bundles.
 * Provides dynamic detection and configuration to ensure secure
 * HTTPS connections across local (Windows) and production (Linux) environments.
 */
class SslHelper
{
    /**
     * Resolve the path to a valid CA certificate bundle.
     * Removes hardcoded local system paths and uses environment variables,
     * ini settings, and vendor-based detection.
     *
     * @return string|bool Returns the path to the CA bundle if found, or false.
     */
    public static function resolveCaBundle(): string|bool
    {
        // 1. Check environment variable CA_BUNDLE_PATH
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH');
        if ($envPath && file_exists((string)$envPath)) {
            return (string)$envPath;
        }

        // 2. Dynamic detection from PHP configuration (ini)
        $iniCurl = ini_get('curl.cainfo');
        if ($iniCurl && file_exists($iniCurl)) {
            return $iniCurl;
        }

        $iniOpenSsl = ini_get('openssl.cafile');
        if ($iniOpenSsl && file_exists($iniOpenSsl)) {
            return $iniOpenSsl;
        }

        // 3. Project-relative vendor paths
        // Assuming project root is where vendor/ exists.
        $appRoot = dirname(__DIR__, 2);
        
        $vendorPaths = [
            $appRoot . '/vendor/guzzlehttp/guzzle/src/cacert.pem',
            $appRoot . '/vendor/razorpay/razorpay/src/cacert.pem',
            $appRoot . '/storage/cacert.pem', // Project storage location
        ];

        foreach ($vendorPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // 4. Common Linux production paths
        $linuxPaths = [
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/ssl/ca-bundle.pem',
            '/usr/local/share/ca-certificates/ca-bundle.crt',
        ];

        foreach ($linuxPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }

    /**
     * Configure PHP's curl and openssl settings to use the resolved CA bundle.
     *
     * @return string|bool Returns the path to the CA bundle if successfully configured, or false.
     */
    public static function configureSslCa(): string|bool
    {
        $path = self::resolveCaBundle();
        if ($path) {
            ini_set('curl.cainfo', (string)$path);
            ini_set('openssl.cafile', (string)$path);
            return (string)$path;
        }
        return false;
    }
}
