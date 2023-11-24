<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\ApiKey;

class ApiKeyProvider
{
    /**
     * @var ApiKey[]
     */
    private array $apiKeys = [];

    public function __construct(
        private readonly Client $usersClient,
        private readonly FrontendTokenProvider $frontendTokenProvider,
    ) {
    }

    /**
     * @param non-empty-string $userEmail
     *
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     */
    public function get(string $userEmail): ApiKey
    {
        if (!array_key_exists($userEmail, $this->apiKeys)) {
            try {
                $apiKey = $this->usersClient->getUserDefaultApiKey(
                    $this->frontendTokenProvider->get($userEmail)->token
                );
            } catch (NonSuccessResponseException | UnauthorizedException) {
                throw new \RuntimeException('API key is null');
            }

            if (null === $apiKey) {
                throw new \RuntimeException('API key is null');
            }

            $this->apiKeys[$userEmail] = $apiKey;
        }

        return $this->apiKeys[$userEmail];
    }
}
