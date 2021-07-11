<?php

namespace Rabble\SnippetBundle\SnippetType\Manager;

use Rabble\ContentBundle\DocumentFieldsProvider\DocumentFieldsProviderInterface;
use Rabble\SnippetBundle\SnippetType\SnippetType;

interface SnippetTypeManagerInterface extends DocumentFieldsProviderInterface
{
    public function add(SnippetType $snippetType): void;

    public function has(string $name): bool;

    public function get(string $name): SnippetType;

    /**
     * @return SnippetType[]
     */
    public function all(): array;

    public function remove(string $name): void;
}
