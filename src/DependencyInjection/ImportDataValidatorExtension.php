<?php

namespace Basilicom\ImportDataValidator\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ImportDataValidatorExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
