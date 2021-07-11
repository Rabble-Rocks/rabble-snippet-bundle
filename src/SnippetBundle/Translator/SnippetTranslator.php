<?php

namespace Rabble\SnippetBundle\Translator;

use Jackalope\Session;
use Rabble\ContentBundle\Content\Transformer\ContentTransformerInterface;
use Rabble\ContentBundle\Content\Translator\AbstractFieldTranslator;
use Rabble\ContentBundle\ContentBlock\ContentBlockManagerInterface;
use Rabble\ContentBundle\Persistence\Document\AbstractPersistenceDocument;
use Rabble\SnippetBundle\Document\Snippet;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManagerInterface;
use Webmozart\Assert\Assert;

class SnippetTranslator extends AbstractFieldTranslator
{
    private SnippetTypeManagerInterface $snippetTypeManager;

    public function __construct(
        SnippetTypeManagerInterface $snippetTypeManager,
        ContentBlockManagerInterface $contentBlockManager,
        Session $session,
        ContentTransformerInterface $contentTransformer
    ) {
        $this->snippetTypeManager = $snippetTypeManager;
        $this->session = $session;
        $this->contentTransformer = $contentTransformer;
        $this->contentBlockManager = $contentBlockManager;
    }

    protected function getFields(AbstractPersistenceDocument $document): array
    {
        Assert::isInstanceOf($document, Snippet::class);

        return $this->snippetTypeManager->get($document->getSnippetType())->getFields();
    }
}
