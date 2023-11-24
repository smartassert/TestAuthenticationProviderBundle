<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\Token;

class ApiTokenProvider
{
    /**
     * @var Token[]
     */
    private array $apiTokens = [];

    public function __construct(
        private readonly Client $usersClient,
        private readonly ApiKeyProvider $apiKeyProvider,
    ) {
    }

    /**
     * @param non-empty-string $userEmail
     *
     * @return non-empty-string
     *
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function get(string $userEmail): string
    {
        if (!array_key_exists($userEmail, $this->apiTokens)) {
            $apiToken = $this->usersClient->createApiToken(
                $this->apiKeyProvider->get($userEmail)->key
            );

            $this->apiTokens[$userEmail] = $apiToken;
        }

        return $this->apiTokens[$userEmail]->token;
    }
}
