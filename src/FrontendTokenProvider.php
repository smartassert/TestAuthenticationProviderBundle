<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * @phpstan-type FrontendToken array{token: non-empty-string, refresh_token: non-empty-string}
 */
class FrontendTokenProvider
{
    /**
     * @var FrontendToken[]
     */
    private array $frontendTokens = [];

    /**
     * @param array<non-empty-string, non-empty-string> $userCredentials
     */
    public function __construct(
        private readonly array $userCredentials,
        private readonly string $baseUrl,
        private readonly ClientInterface $httpClient,
    ) {}

    /**
     * @param non-empty-string $userEmail
     *
     * @return FrontendToken
     *
     * @throws ClientExceptionInterface
     */
    public function get(string $userEmail): array
    {
        if (!array_key_exists($userEmail, $this->frontendTokens)) {
            $this->frontendTokens[$userEmail] = $this->retrieve($userEmail);
        }

        return $this->frontendTokens[$userEmail];
    }

    /**
     * @return FrontendToken
     *
     * @throws ClientExceptionInterface
     */
    private function retrieve(string $email): array
    {
        $request = new Request(
            'POST',
            $this->baseUrl . '/frontend-token/create',
            ['content-type' => 'application/json'],
            (string) json_encode([
                'username' => $email,
                'password' => $this->userCredentials[$email] ?? '',
            ])
        );

        $response = $this->httpClient->sendRequest($request);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Invalid user credentials.');
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new \RuntimeException('Invalid user credentials.');
        }

        $token = $responseData['token'] ?? null;
        if (!is_string($token) || '' === $token) {
            throw new \RuntimeException('Invalid user credentials.');
        }

        $refreshToken = $responseData['refresh_token'] ?? null;
        if (!is_string($refreshToken) || '' === $refreshToken) {
            throw new \RuntimeException('Invalid user credentials.');
        }

        return ['token' => $token, 'refresh_token' => $refreshToken];
    }
}
