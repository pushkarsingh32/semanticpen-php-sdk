<?php

namespace SemanticPen\SDK\Core;

use SemanticPen\SDK\Exceptions\AuthenticationException;
use SemanticPen\SDK\Exceptions\NetworkException;
use SemanticPen\SDK\Exceptions\RateLimitException;
use SemanticPen\SDK\Exceptions\SemanticPenException;

/**
 * Base HTTP client for SemanticPen API
 */
class BaseClient
{
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;
    protected $debug;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['apiKey'] ?? '';
        $this->baseUrl = rtrim($config['baseUrl'] ?? 'https://www.semanticpen.com', '/');
        $this->timeout = $config['timeout'] ?? 30;
        $this->debug = $config['debug'] ?? false;

        if (empty($this->apiKey)) {
            throw new AuthenticationException('API key is required');
        }
    }

    protected function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    protected function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        if ($this->debug) {
            error_log("SemanticPen SDK: {$method} {$url}");
            if (!empty($data)) {
                error_log("SemanticPen SDK: Request data: " . json_encode($data));
            }
        }

        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: SemanticPen-PHP-SDK/1.0.0'
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($response === false || !empty($error)) {
            throw new NetworkException("Network error: {$error}");
        }

        if ($this->debug) {
            error_log("SemanticPen SDK: Response code: {$httpCode}");
            error_log("SemanticPen SDK: Response body: " . substr($response, 0, 500));
        }

        $decodedResponse = $this->parseResponse($response, $httpCode);

        if ($httpCode >= 400) {
            $this->handleErrorResponse($httpCode, $decodedResponse);
        }

        return $decodedResponse;
    }

    private function parseResponse(string $response, int $httpCode): array
    {
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($httpCode >= 400) {
                return ['error' => $response ?: 'Unknown error occurred'];
            }
            return ['data' => $response];
        }
        
        return $decoded ?: [];
    }

    private function handleErrorResponse(int $httpCode, array $response): void
    {
        $message = $response['error'] ?? $response['message'] ?? 'Unknown error occurred';
        $details = $response['details'] ?? [];

        switch ($httpCode) {
            case 401:
            case 403:
                throw new AuthenticationException($message, $httpCode, $details);
                
            case 429:
                $retryAfter = (int)($response['retryAfter'] ?? 0);
                throw new RateLimitException($message, $retryAfter, $details);
                
            default:
                throw new NetworkException($message, $httpCode, $details);
        }
    }
}