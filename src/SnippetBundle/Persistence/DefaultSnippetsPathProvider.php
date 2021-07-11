<?php

namespace Rabble\SnippetBundle\Persistence;

use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;
use Rabble\ContentBundle\Persistence\Provider\PathProviderInterface;
use Rabble\SnippetBundle\Document\DefaultSnippets;

class DefaultSnippetsPathProvider implements PathProviderInterface
{
    public const ROOT_NODE = '/defaut-snippets';

    public function provide(AbstractPersistenceDocument $document): string
    {
        if (!$document instanceof DefaultSnippets) {
            throw new \InvalidArgumentException(sprintf('Expected an instance of DefaultSnippets. Got: %s', get_class($document)));
        }

        return sprintf('%s/%s', self::ROOT_NODE, $document->getNodeName());
    }
}
