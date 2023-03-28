<?php

declare(strict_types=1);

namespace SmartAssert\TestAuthenticationProviderBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class TestAuthenticationProviderBundle extends AbstractBundle
{
    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::loadExtension($config, $container, $builder);

        $container->import('../config/services.yaml');
        if ($this->isInSelfTestEnvironment($builder)) {
            $container->import('../config/services_test.yaml');
        }
    }

    private function isInSelfTestEnvironment(ContainerBuilder $builder): bool
    {
        if ('test' !== $builder->getParameter('kernel.environment')) {
            return false;
        }

        $kernelProjectDirectory = $builder->getParameter('kernel.project_dir');
        if (!is_string($kernelProjectDirectory)) {
            $kernelProjectDirectory = '';
        }

        $bundleDirectory = realpath(__DIR__ . '/..');

        return $kernelProjectDirectory === $bundleDirectory;
    }
}
