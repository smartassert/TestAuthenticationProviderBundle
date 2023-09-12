<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests\Functional;

use PHPUnit\Framework\TestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new TestingKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    /**
     * @dataProvider serviceExistsInContainerDataProvider
     *
     * @param class-string $id
     */
    public function testServiceExistsInContainer(string $id): void
    {
        self::assertInstanceOf($id, $this->container->get($id));
    }

    /**
     * @return array<mixed>
     */
    public static function serviceExistsInContainerDataProvider(): array
    {
        return [
            UserProvider::class => [
                'id' => UserProvider::class,
            ],
            ApiTokenProvider::class => [
                'id' => ApiTokenProvider::class,
            ],
            FrontendTokenProvider::class => [
                'id' => FrontendTokenProvider::class,
            ],
            ApiKeyProvider::class => [
                'id' => ApiKeyProvider::class,
            ],
        ];
    }
}
