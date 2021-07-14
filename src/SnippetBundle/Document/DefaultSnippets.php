<?php

namespace Rabble\SnippetBundle\Document;

use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;

class DefaultSnippets extends AbstractPersistenceDocument
{
    public const ROOT_NODE = '/default-snippets';
    public const DEFAULT_NODE_NAME = 'default-snippets';

    protected array $defaults = [];

    public function __construct()
    {
        $this->nodeName = self::DEFAULT_NODE_NAME;
    }

    public static function getOwnProperties(): array
    {
        return ['defaults'];
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults): void
    {
        $this->dirty = true;
        $this->defaults = $defaults;
    }
}
