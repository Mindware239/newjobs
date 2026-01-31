<?php

declare(strict_types=1);

namespace App\Services;

use Google_Client;
use Google_Service_Oauth2;
use Exception;

class GoogleOAuthService
{
    private Google_Client $client;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        $this->client = new Google_Client();
        $this->client->setClientId($this->config['client_id']);
        $this->client->setClientSecret($this->config['client_secret']);
        $this->client->setRedirectUri($this->config['redirect_uri']);
        
        // Add scopes
        foreach ($this->config['scopes'] as $scope) {
            $this->client->addScope($scope);
        }
        
        $this->client->setAccessType('online');
        $this->client->setPrompt('select_account');
        
        // Fix SSL certificate issue for XAMPP/Windows (development only)
        // In production, configure proper SSL certificates in php.ini
        if (file_exists(__DIR__ . '/../../vendor/guzzlehttp/guzzle/src/Client.php')) {
            try {
                $httpClient = new \GuzzleHttp\Client([
                    'verify' => $this->getCaBundlePath()
                ]);
                $this->client->setHttpClient($httpClient);
            } catch (\Exception $e) {
                // If Guzzle not available, continue without custom HTTP client
                error_log("Could not set custom HTTP client: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get CA bundle path for SSL verification
     * Tries multiple common locations
     */
    private function getCaBundlePath(): string|bool
    {
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH') ?: null;
        $possiblePaths = [
            $envPath,
            __DIR__ . '/../../vendor/guzzlehttp/guzzle/src/cacert.pem',
            'E:/xampp/php/extras/ssl/cacert.pem',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'E:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];
        
        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }
        
        return true;
    }

    public function getAuthUrl(string $state = null): string
    {
        if ($state) {
            $this->client->setState($state);
        }
        return $this->client->createAuthUrl();
    }

    public function fetchAccessTokenWithCode(string $code): array
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($token['error'])) {
                throw new Exception('Token exchange failed: ' . $token['error']);
            }
            
            return $token;
        } catch (Exception $e) {
            error_log("Google OAuth token error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserInfo(array $token): array
    {
        try {
            $this->client->setAccessToken($token);
            
            if ($this->client->isAccessTokenExpired()) {
                throw new Exception('Access token expired');
            }
            
            $oauth2 = new Google_Service_Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();
            
            return [
                'id' => $userInfo->getId(),
                'email' => $userInfo->getEmail(),
                'name' => $userInfo->getName(),
                'picture' => $userInfo->getPicture(),
                'verified_email' => $userInfo->getVerifiedEmail(),
                'given_name' => $userInfo->getGivenName(),
                'family_name' => $userInfo->getFamilyName()
            ];
        } catch (Exception $e) {
            error_log("Google user info error: " . $e->getMessage());
            throw $e;
        }
    }

    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

