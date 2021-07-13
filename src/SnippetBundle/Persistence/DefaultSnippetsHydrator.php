<?php

namespace Rabble\SnippetBundle\Persistence;

use Jackalope\Node;
use Jackalope\Session;
use Rabble\ContentBundle\Content\Transformer\ContentTransformerInterface;
use Rabble\ContentBundle\Exception\InvalidContentDocumentException;
use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;
use Rabble\ContentBundle\Persistence\Hydrator\DocumentHydratorInterface;
use Rabble\ContentBundle\Persistence\Hydrator\ReflectionHydrator;
use Rabble\SnippetBundle\Document\DefaultSnippets;

class DefaultSnippetsHydrator implements DocumentHydratorInterface
{
    private Session $session;
    private ReflectionHydrator $baseHydrator;

    public function __construct(
        Session $session,
        ReflectionHydrator $baseHydrator
    ) {
        $this->session = $session;
        $this->baseHydrator = $baseHydrator;
    }

    public function hydrateDocument(AbstractPersistenceDocument $document, ?Node $node = null): void
    {
        $node = $node ?? $this->session->getObjectManager()->getNodeByPath($document->getPath());
        if (!$node instanceof Node || !$document instanceof DefaultSnippets) {
            throw new InvalidContentDocumentException();
        }
        $this->baseHydrator->hydrateDocument($document, $node);
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
            throw new InvalidContentDocumentException();
        }
        $this->baseHydrator->hydrateNode($document, $node);
        $defaultsNode = $node->hasNode('defaults') ? $node->getNode('defaults') : $node->addNode('defaults');
        foreach ($document->getDefaults() as $key => $value) {
            $defaultsNode->setProperty($key, $value);
        }
    }
}
