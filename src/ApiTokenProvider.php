<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;

class ApiTokenProvider
{
    /**
     * @var Token[]
     */
    private array $apiTokens = [];

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
     * @return non-empty-string
     *
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     */
    public function get(string $userEmail): string
    {
        if (!array_key_exists($userEmail, $this->apiTokens)) {
            $apiToken = $this->usersClient->createApiToken(
                $this->getApiKey($userEmail)->key
            );

            $this->apiTokens[$userEmail] = $apiToken;
        }

        return $this->apiTokens[$userEmail]->token;
    }

    /**
     * @param non-empty-string $userEmail
     *
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     */
    private function getApiKey(string $userEmail): ApiKey
    {
        if (!array_key_exists($userEmail, $this->apiKeys)) {
            try {
                $apiKeys = $this->usersClient->listUserApiKeys(
                    $this->frontendTokenProvider->get($userEmail)
                );

                $apiKey = $apiKeys->getDefault();
                if (null === $apiKey) {
                    throw new \RuntimeException('API key is null');
                }

                $this->apiKeys[$userEmail] = $apiKey;
            } catch (\RuntimeException $e) {
                if ('Invalid user credentials.' === $e->getMessage()) {
                    throw new \RuntimeException('API key is null');
                }
            } catch (NonSuccessResponseException $e) {
                throw new \RuntimeException('API key is null');
            }
        }

        return $this->apiKeys[$userEmail];
    }
}
