<?php

namespace PaneeDesign\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PedApiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (array_key_exists('host', $config) === false) {
            $this->printException('ped_api.host', ($config['key']));
        }

        $container->setParameter('ped_api.host', $config['host']);

        if (array_key_exists('type', $config) === true) {
            $container->setParameter('ped_api.type', $config['type']);
        }

        if (array_key_exists('client', $config) === true) {
            $client = $config['client'];

            if (array_key_exists('id', $client) === false) {
                $this->printException('ped_api.client.id');
            }

            $container->setParameter('ped_api.client.id', $client['id']);

            if (array_key_exists('secret', $client) === false) {
                $this->printException('ped_api.client.secret');
            }

            $container->setParameter('ped_api.client.secret', $client['secret']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function printException($name, $toPrint = true)
    {
        if ($toPrint) {
            throw new \InvalidArgumentException(sprintf('The option "%s" must be set.', $name));
        }
    }
}
