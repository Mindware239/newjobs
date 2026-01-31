<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AppleOAuthService
{
    private array $config;
    private ?string $cachedClientSecret = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Generate Apple client secret (JWT)
     * Apple requires a JWT signed with ES256 (ECDSA) algorithm
     * The JWT is valid for 6 months, so we cache it
     */
    public function getClientSecret(): string
    {
        // Cache the client secret as it's valid for 6 months
        if ($this->cachedClientSecret !== null) {
            return $this->cachedClientSecret;
        }

        if (empty($this->config['team_id']) || 
            empty($this->config['client_id']) || 
            empty($this->config['key_id']) || 
            empty($this->config['private_key'])) {
            throw new Exception('Apple OAuth configuration incomplete');
        }

        $privateKey = $this->config['private_key'];
        
        // If private_key is a file path, read it
        if (file_exists($privateKey)) {
            $privateKeyContent = file_get_contents($privateKey);
        } else {
            $privateKeyContent = $privateKey;
        }

        $now = time();
        $header = [
            'alg' => 'ES256',
            'kid' => $this->config['key_id']
        ];

        $payload = [
            'iss' => $this->config['team_id'],
            'iat' => $now,
            'exp' => $now + (180 * 24 * 60 * 60), // 180 days (6 months)
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->config['client_id']
        ];

        try {
            // Generate JWT using Firebase JWT library with ES256
            $jwt = JWT::encode($payload, $privateKeyContent, 'ES256', $this->config['key_id']);
            
            // Cache it
            $this->cachedClientSecret = $jwt;
            
            return $jwt;
        } catch (Exception $e) {
            error_log("Apple JWT generation error: " . $e->getMessage());
            throw new Exception('Failed to generate Apple client secret: ' . $e->getMessage());
        }
    }

    public function getAuthUrl(string $state = null): string
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', $this->config['scopes']),
            'response_mode' => 'form_post'
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return 'https://appleid.apple.com/auth/authorize?' . http_build_query($params);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $clientSecret = $this->getClientSecret();

        $tokenUrl = 'https://appleid.apple.com/auth/token';
        $tokenData = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->config['redirect_uri']
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: JobPortal/1.0'
        ]);

        $tokenResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Apple OAuth token error (HTTP $httpCode): " . $tokenResponse);
            if ($curlError) {
                error_log("CURL error: " . $curlError);
            }
            throw new Exception('Failed to exchange code for token. HTTP ' . $httpCode);
        }

        $tokenData = json_decode($tokenResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Apple');
        }

        if (isset($tokenData['error'])) {
            throw new Exception('Apple token error: ' . $tokenData['error_description'] ?? $tokenData['error']);
        }

        return $tokenData;
    }

    public function decodeIdToken(string $idToken): array
    {
        try {
            // Decode without verification first to get the payload
            // In production, you should verify the signature using Apple's public keys
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                throw new Exception('Invalid ID token format');
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to decode ID token payload');
            }

            // Basic validation
            if (empty($payload['sub'])) {
                throw new Exception('Invalid ID token: missing subject');
            }

            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new Exception('ID token has expired');
            }

            return $payload;
        } catch (Exception $e) {
            error_log("Apple ID token decode error: " . $e->getMessage());
            throw $e;
        }
    }

    public function parseUserData(string $userDataJson): array
    {
        if (empty($userDataJson)) {
            return [];
        }

        try {
            $userData = json_decode($userDataJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            return [
                'name' => trim(($userData['name']['firstName'] ?? '') . ' ' . ($userData['name']['lastName'] ?? '')),
                'email' => $userData['email'] ?? null
            ];
        } catch (Exception $e) {
            error_log("Apple user data parse error: " . $e->getMessage());
            return [];
        }
    }

    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

