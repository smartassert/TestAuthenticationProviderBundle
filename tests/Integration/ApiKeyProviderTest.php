<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\Tests\Functional\TestingKernel;
use SmartAssert\UsersClient\Model\ApiKey;

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
     * @dataProvider getSuccessDataProvider
     *
     * @param non-empty-string $userEmail
     */
    public function testGetSuccess(string $userEmail): void
    {
        $apiKey = $this->apiKeyProvider->get($userEmail);

        self::assertInstanceOf(ApiKey::class, $apiKey);
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
            self::fail('\\RuntimeException not thrown');
        } catch (\RuntimeException $e) {
            self::assertSame('Invalid user credentials.', $e->getMessage());
        }
    }
}
