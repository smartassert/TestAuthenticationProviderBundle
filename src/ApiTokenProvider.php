<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class ApiTokenProvider
{
    /**
     * @var non-empty-string[]
     */
    private array $apiTokens = [];

    public function __construct(
        private readonly string $baseUrl,
        private readonly ClientInterface $httpClient,
        private readonly ApiKeyProvider $apiKeyProvider,
    ) {
    }

    /**
     * @param non-empty-string $userEmail
     *
     * @return non-empty-string
     *
     * @throws ClientExceptionInterface
     */
    public function get(string $userEmail): string
    {
        if (!array_key_exists($userEmail, $this->apiTokens)) {
            $this->apiTokens[$userEmail] = $this->retrieve($userEmail);
        }

        return $this->apiTokens[$userEmail];
    }

    /**
     * @param non-empty-string $email
     *
     * @return non-empty-string
     *
     * @throws ClientExceptionInterface
     */
    private function retrieve(string $email): string
    {
        $apiKey = $this->apiKeyProvider->get($email);

        $request = new Request(
            'POST',
            $this->baseUrl . '/api-token/create',
            ['authorization' => $apiKey['key']]
        );

        $response = $this->httpClient->sendRequest($request);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Api token not created.');
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new \RuntimeException('Api token not created.');
        }

        $token = $responseData['token'] ?? null;
        $token = is_string($token) ? trim($token) : null;
        if ('' === $token || null === $token) {
            throw new \RuntimeException('Api token not created.');
        }

        return $token;
    }
}
