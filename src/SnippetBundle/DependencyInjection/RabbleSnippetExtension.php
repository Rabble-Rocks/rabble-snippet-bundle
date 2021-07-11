<?php

namespace Rabble\SnippetBundle\DependencyInjection;

use Rabble\SnippetBundle\DependencyInjection\Configurator\SnippetTypeConfigurator;
use Rabble\SnippetBundle\Document\Snippet;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManager;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManagerInterface;
use Rabble\SnippetBundle\SnippetType\SnippetType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class RabbleSnippetExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $this->registerSnippetTypeConfigurator($container);
        $this->registerSnippetTypeManager($config, $container);
        $this->registerIndexable($container);
    }

    private function registerSnippetTypeConfigurator(ContainerBuilder $container)
    {
        $configuratorDef = new Definition(SnippetTypeConfigurator::class, [
            new Reference('rabble_field_type.field_type_mapping_collection'),
        ]);
        $container->setDefinition(SnippetTypeConfigurator::class, $configuratorDef);
    }

    private function registerSnippetTypeManager(array $config, ContainerBuilder $container)
    {
        $snippetTypeManagerDef = new Definition(SnippetTypeManager::class);
        $container->setDefinition($snippetTypeManagerId = 'rabble_snippet.snippet_type_manager', $snippetTypeManagerDef);
        $container->addAliases([
            'snippet_type_manager' => $snippetTypeManagerId,
            SnippetTypeManagerInterface::class => $snippetTypeManagerId,
            SnippetTypeManager::class => $snippetTypeManagerId,
        ]);
        $snippetTypeManagerDef->addTag('rabble_content.document_fields_provider');
        $this->addSnippetTypes($config, $container, $snippetTypeManagerDef);
    }

    private function addSnippetTypes(array $config, ContainerBuilder $container, Definition $snippetTypeManagerDef)
    {
        foreach ($config['types'] as $snippetType) {
            $snippetTypeId = sprintf('rabble_snippet.snippet_type.%s', $snippetType['name']);
            $container->setDefinition($snippetTypeId, $snippetTypeDef = new Definition(SnippetType::class, [
                $snippetType['name'],
                $snippetType['attributes'],
            ]));
            $snippetTypeDef->addMethodCall('setFields', [$snippetType['fields']]);
            $snippetTypeDef->setConfigurator([new Reference(SnippetTypeConfigurator::class), 'configure']);
            $snippetTypeManagerDef->addMethodCall('add', [new Reference($snippetTypeId)]);
        }
    }

    private function registerIndexable(ContainerBuilder $container): void
    {
        $indexables = $container->hasParameter('rabble_content.indexables') ?
            $container->getParameter('rabble_content.indexables') : [];

        $indexables['snippet'] = Snippet::class;
        $container->setParameter('rabble_content.indexables', $indexables);
    }
}
