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
    private ContentTransformerInterface $contentTransformer;
    private ReflectionHydrator $baseHydrator;

    public function __construct(
        Session $session,
        ContentTransformerInterface $contentTransformer,
        ReflectionHydrator $baseHydrator
    ) {
        $this->session = $session;
        $this->contentTransformer = $contentTransformer;
        $this->baseHydrator = $baseHydrator;
    }

    public function hydrateDocument(AbstractPersistenceDocument $document, ?Node $node = null): void
    {
        $node = $node ?? $this->session->getObjectManager()->getNodeByPath($document->getPath());
        if (!$node instanceof Node || !$document instanceof DefaultSnippets) {
            throw new InvalidContentDocumentException();
        }
        $this->baseHydrator->hydrateDocument($document, $node);
        $data = $this->contentTransformer->getData($node);
        if (isset($data['defaults'])) {
            $document->setDefaults($data['defaults']);
        }
    }

    public function hydrateNode(AbstractPersistenceDocument $document, Node $node): void
    {
        if (!$document instanceof DefaultSnippets) {
            throw new InvalidContentDocumentException();
        }
        $this->baseHydrator->hydrateNode($document, $node);
        $data = [
            'defaults' => $document->getDefaults(),
        ];
        $this->contentTransformer->setData($node, $data);
    }
}
