<?php

namespace Rabble\SnippetBundle\Persistence;

use Jackalope\Node;
use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;
use Rabble\ContentBundle\Persistence\Hydrator\DocumentHydratorInterface;
use Rabble\SnippetBundle\Document\DefaultSnippets;

class DefaultSnippetsHydrator implements DocumentHydratorInterface
{
    public function hydrateDocument(AbstractPersistenceDocument $document, Node $node): void
    {
        if (!$document instanceof DefaultSnippets) {
            return;
        }
        if ($node->hasNode('defaults')) {
            $defaultsNode = $node->getNode('defaults');
            $defaults = [];
            foreach ($defaultsNode->getPropertiesValues() as $key => $value) {
                if (false === strpos($key, 'jcr:')) {
                    $defaults[$key] = $value;
                }
            }
            $document->setDefaults($defaults);
        }
    }

    public function hydrateNode(AbstractPersistenceDocument $document, Node $node): void
    {
        if (!$document instanceof DefaultSnippets) {
            return;
        }
        $defaultsNode = $node->hasNode('defaults') ? $node->getNode('defaults') : $node->addNode('defaults');
        foreach ($document->getDefaults() as $key => $value) {
            $defaultsNode->setProperty($key, $value);
        }
    }
}
