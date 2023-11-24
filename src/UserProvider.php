<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\User;

class UserProvider
{
    /**
     * @var User[]
     */
    private array $users = [];

    public function __construct(
        private readonly Client $usersClient,
        private readonly FrontendCredentialsProvider $frontendTokenProvider,
    ) {
    }

    /**
     * @param non-empty-string $userEmail
     *
     * @throws ClientExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     * @throws \RuntimeException
     */
    public function get(string $userEmail): User
    {
        try {
            $frontendToken = $this->frontendTokenProvider->get($userEmail);
        } catch (\RuntimeException) {
            throw new \RuntimeException('User is null');
        }

        if (!array_key_exists($userEmail, $this->users)) {
            $user = $this->usersClient->verifyFrontendToken($frontendToken->token);

            if (null === $user) {
                throw new \RuntimeException('User is null');
            }

            $this->users[$userEmail] = $user;
        }

        return $this->users[$userEmail];
    }
}
