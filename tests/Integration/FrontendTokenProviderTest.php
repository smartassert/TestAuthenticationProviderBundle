<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\Tests\Functional\TestingKernel;
use SmartAssert\UsersClient\Model\RefreshableToken;

class FrontendTokenProviderTest extends TestCase
{
    private FrontendTokenProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $provider = $container->get(FrontendTokenProvider::class);
        \assert($provider instanceof FrontendTokenProvider);
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

        self::assertInstanceOf(RefreshableToken::class, $frontendToken);
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
