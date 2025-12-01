<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\Tests\Functional\TestingKernel;

class ApiKeyProviderTest extends TestCase
{
    private ApiKeyProvider $apiKeyProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $apiKeyProvider = $container->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $this->apiKeyProvider = $apiKeyProvider;
    }

    /**
     * @param non-empty-string $userEmail
     */
    #[DataProvider('getSuccessDataProvider')]
    public function testGetSuccess(string $userEmail): void
    {
        $apiKey = $this->apiKeyProvider->get($userEmail);

        self::assertNotEmpty($apiKey['key']);
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
            $this->apiKeyProvider->get('undefined@example.com');
            self::fail('\RuntimeException not thrown');
        } catch (\RuntimeException $e) {
            self::assertSame('User is null', $e->getMessage());
        }
    }
}
