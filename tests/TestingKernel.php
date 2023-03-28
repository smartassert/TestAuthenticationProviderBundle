<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle\Tests;

use SmartAssert\TestAuthenticationProviderBundle\TestAuthenticationProviderBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestingKernel extends Kernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [
            new TestAuthenticationProviderBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }
}
