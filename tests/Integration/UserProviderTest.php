<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\Tests\Functional\TestingKernel;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;
use SmartAssert\UsersClient\Model\User;

class UserProviderTest extends TestCase
{
    private UserProvider $userProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $userProvider = $container->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $this->userProvider = $userProvider;
    }

    /**
     * @dataProvider getSuccessDataProvider
     *
     * @param non-empty-string $userEmail
     */
    public function testGetSuccess(string $userEmail): void
    {
        $user = $this->userProvider->get($userEmail);
        self::assertInstanceOf(User::class, $user);
        self::assertSame($userEmail, $user->userIdentifier);
    }

    /**
     * @return array<mixed>
     */
    public function getSuccessDataProvider(): array
    {
        return [
            'user1' => [
                'userEmail' => 'user1@example.com',
            ],
            'user2' => [
                'userEmail' => 'user2@example.com',
            ],
        ];
    }

    public function testGetUserNotDefined(): void
    {
        try {
            $this->userProvider->get('undefined@example.com');
            self::fail('\\RuntimeException not thrown');
        } catch (\RuntimeException $e) {
            self::assertSame('User is null', $e->getMessage());
        }
    }
}
