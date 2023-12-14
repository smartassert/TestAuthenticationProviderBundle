<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * @phpstan-type ApiKey array{label: ?non-empty-string, key: non-empty-string}
 */
class ApiKeyProvider
{
    /**
     * @var ApiKey[]
     */
    private array $apiKeys = [];

    public function __construct(
        private readonly string $baseUrl,
        private readonly ClientInterface $httpClient,
        private readonly FrontendTokenProvider $frontendTokenProvider,
    ) {
    }

    /**
     * @param non-empty-string $userEmail
     *
     * @return ApiKey
     *
     * @throws ClientExceptionInterface
     */
    public function get(string $userEmail): array
    {
        if (!array_key_exists($userEmail, $this->apiKeys)) {
            $this->apiKeys[$userEmail] = $this->retrieve($userEmail);
        }

        return $this->apiKeys[$userEmail];
    }

    /**
     * @param non-empty-string $email
     *
     * @return ApiKey
     *
     * @throws ClientExceptionInterface
     */
    private function retrieve(string $email): array
    {
        try {
            $frontendToken = $this->frontendTokenProvider->get($email);
        } catch (\RuntimeException) {
            throw new \RuntimeException('User is null');
        }

        $request = new Request(
            'GET',
            $this->baseUrl . '/apikey',
            ['authorization' => 'Bearer ' . $frontendToken['token']]
        );

        $response = $this->httpClient->sendRequest($request);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('API key is null');
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new \RuntimeException('API key is null');
        }

        $label = $responseData['label'] ?? null;
        $label = is_string($label) ? $label : null;
        if ('' === $label) {
            throw new \RuntimeException('API key is null');
        }

        $key = $responseData['key'] ?? null;
        if (!is_string($key) || '' === $key) {
            throw new \RuntimeException('API key is null');
        }

        return ['label' => $label, 'key' => $key];
    }
}
