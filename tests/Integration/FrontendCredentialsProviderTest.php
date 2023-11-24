<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\FrontendCredentialsProvider;
use SmartAssert\TestAuthenticationProviderBundle\Tests\Functional\TestingKernel;
use SmartAssert\UsersClient\Model\FrontendCredentials;

class FrontendCredentialsProviderTest extends TestCase
{
    private FrontendCredentialsProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $provider = $container->get(FrontendCredentialsProvider::class);
        \assert($provider instanceof FrontendCredentialsProvider);
        $this->provider = $provider;
    }

    /**
     * @dataProvider getSuccessDataProvider
     *
     * @param non-empty-string $userEmail
     */
    public function testGetSuccess(string $userEmail): void
    {
        $frontendToken = $this->provider->get($userEmail);

        self::assertInstanceOf(FrontendCredentials::class, $frontendToken);
    }

    /**
     * @return array<mixed>
     */
    public static function getSuccessDataProvider(): array
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
            $this->provider->get('undefined@example.com');
            self::fail('\\RuntimeException not thrown');
        } catch (\RuntimeException $e) {
            self::assertSame('Invalid user credentials.', $e->getMessage());
        }
    }
}
