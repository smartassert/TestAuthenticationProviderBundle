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
use SmartAssert\UsersClient\Model\FrontendCredentials;

class FrontendCredentialsProvider
{
    /**
     * @var FrontendCredentials[]
     */
    private array $frontendTokens = [];

    /**
     * @param array<non-empty-string, non-empty-string> $userCredentials
     */
    public function __construct(
        private readonly array $userCredentials,
        private readonly Client $usersClient,
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
    public function get(string $userEmail): FrontendCredentials
    {
        if (!array_key_exists($userEmail, $this->frontendTokens)) {
            try {
                $this->frontendTokens[$userEmail] = $this->usersClient->createFrontendCredentials(
                    $userEmail,
                    $this->userCredentials[$userEmail] ?? ''
                );
            } catch (NonSuccessResponseException | UnauthorizedException) {
                throw new \RuntimeException('Invalid user credentials.');
            }
        }

        return $this->frontendTokens[$userEmail];
    }
}
