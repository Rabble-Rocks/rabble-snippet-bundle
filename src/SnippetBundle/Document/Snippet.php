<?php

namespace Rabble\SnippetBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use Rabble\ContentBundle\Annotation\NodeName;
use Rabble\ContentBundle\Annotation\NodeProperty;
use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;

/**
 * @ES\Index()
 */
class Snippet extends AbstractPersistenceDocument
{
    public const ROOT_NODE = '/snippet';

    /**
     * @NodeProperty("jcr:uuid")
     * @ES\Id
     */
    protected string $uuid;

    /**
     * @ES\Property
     */
    protected array $properties;

    /**
     * @NodeName()
     * @ES\Property(type="text", analyzer="case_insensitive", fields={"keyword"={"type"="keyword"}})
     */
    protected string $title;

    /**
     * @NodeProperty("rabble:snippet_type")
     */
    protected string $snippetType;

    public static function getOwnProperties(): array
    {
        return ['title', 'snippetType'];
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->dirty = true;
        $this->title = $title;
    }

    public function getSnippetType(): string
    {
        return $this->snippetType;
    }

    public function setSnippetType(string $snippetType): void
    {
        $this->snippetType = $snippetType;
    }
}
