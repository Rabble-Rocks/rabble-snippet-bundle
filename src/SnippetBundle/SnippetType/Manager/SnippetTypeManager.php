<?php

namespace Rabble\SnippetBundle\SnippetType\Manager;

use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;
use Rabble\SnippetBundle\Document\Snippet;
use Rabble\SnippetBundle\SnippetType\SnippetType;

class SnippetTypeManager implements SnippetTypeManagerInterface
{
    /** @var SnippetType[] */
    protected array $snippetTypes = [];

    /**
     * SnippetTypeManager constructor.
     *
     * @param SnippetType[] $snippetTypes
     */
    public function __construct(array $snippetTypes = [])
    {
        foreach ($snippetTypes as $snippetType) {
            $this->add($snippetType);
        }
    }

    public function add(SnippetType $snippetType): void
    {
        $this->snippetTypes[$snippetType->getName()] = $snippetType;
    }

    public function has(string $name): bool
    {
        return isset($this->snippetTypes[$name]);
    }

    public function get(string $name): SnippetType
    {
        return $this->snippetTypes[$name];
    }

    public function all(): array
    {
        return $this->snippetTypes;
    }

    public function remove(string $name): void
    {
        unset($this->snippetTypes[$name]);
    }

    public function getFields(AbstractPersistenceDocument $document): ?array
    {
        if (!$document instanceof Snippet) {
            return null;
        }
        $snippetType = $this->get($document->getSnippetType());

        return $snippetType->getFields();
    }
}
