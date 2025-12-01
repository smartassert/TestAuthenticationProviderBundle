<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\Tests\Functional\TestingKernel;

class ApiTokenProviderTest extends TestCase
{
    private ApiTokenProvider $apiTokenProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $userProvider = $container->get(ApiTokenProvider::class);
        \assert($userProvider instanceof ApiTokenProvider);
        $this->apiTokenProvider = $userProvider;
    }

    /**
     * @param non-empty-string $userEmail
     */
    #[DataProvider('getSuccessDataProvider')]
    public function testGetSuccess(string $userEmail): void
    {
        $apiToken = $this->apiTokenProvider->get($userEmail);

        self::assertIsString($apiToken);
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
            $this->apiTokenProvider->get('undefined@example.com');
            self::fail('\RuntimeException not thrown');
        } catch (\RuntimeException $e) {
            self::assertSame('User is null', $e->getMessage());
        }
    }
}
