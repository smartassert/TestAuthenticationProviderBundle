<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * @phpstan-type User array{id: non-empty-string, user_identifier: non-empty-string}
 */
class UserProvider
{
    /**
     * @var User[]
     */
    private array $users = [];

    public function __construct(
        private readonly string $baseUrl,
        private readonly ClientInterface $httpClient,
        private readonly FrontendTokenProvider $frontendTokenProvider,
    ) {}

    /**
     * @param non-empty-string $userEmail
     *
     * @return User
     *
     * @throws ClientExceptionInterface
     */
    public function get(string $userEmail): array
    {
        if (!array_key_exists($userEmail, $this->users)) {
            $this->users[$userEmail] = $this->retrieve($userEmail);
        }

        return $this->users[$userEmail];
    }

    /**
     * @param non-empty-string $email
     *
     * @return User
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
            $this->baseUrl . '/frontend-token/verify',
            ['authorization' => 'Bearer ' . $frontendToken['token']]
        );

        $response = $this->httpClient->sendRequest($request);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('User is null');
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new \RuntimeException('User is null');
        }

        $id = $responseData['id'] ?? null;
        if (!is_string($id) || '' === $id) {
            throw new \RuntimeException('User is null');
        }

        $userIdentifier = $responseData['user-identifier'] ?? null;
        if (!is_string($userIdentifier) || '' === $userIdentifier) {
            throw new \RuntimeException('User is null');
        }

        return ['id' => $id, 'user_identifier' => $userIdentifier];
    }
}
