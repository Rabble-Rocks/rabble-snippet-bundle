<?php

namespace Rabble\SnippetBundle\Persistence;

use Jackalope\Session;
use PHPCR\RepositoryException;
use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;
use Rabble\ContentBundle\Persistence\Provider\NodeName\NodeNameProviderInterface;
use Rabble\ContentBundle\Persistence\Provider\PathProviderInterface;
use Rabble\SnippetBundle\Document\Snippet;
use Symfony\Component\String\Slugger\SluggerInterface;

class SnippetPathProvider implements PathProviderInterface
{
    public const ROOT_NODE = '/snippet';

    private NodeNameProviderInterface $nodeNameProvider;
    private SluggerInterface $slugger;
    private Session $session;

    public function __construct(
        NodeNameProviderInterface $nodeNameProvider,
        SluggerInterface $slugger,
        Session $session
    ) {
        $this->nodeNameProvider = $nodeNameProvider;
        $this->slugger = $slugger;
        $this->session = $session;
    }

    public function provide(AbstractPersistenceDocument $document): string
    {
        if (!$document instanceof Snippet) {
            throw new \InvalidArgumentException(sprintf('Expected an instance of Snippet. Got: %s', get_class($document)));
        }
        $nodeName = $this->slugger->slug($this->nodeNameProvider->provide($document), '-');
        $suffix = '';
        for ($i = 1; $this->hasCollision($path = sprintf('%s/%s', self::ROOT_NODE, $nodeName.$suffix)); ++$i) {
            $suffix = "-{$i}";
        }

        return $path;
    }

    private function hasCollision($path): bool
    {
        try {
            $this->session->getNode($path);
        } catch (RepositoryException $exception) {
            return false;
        }

        return true;
    }
}
