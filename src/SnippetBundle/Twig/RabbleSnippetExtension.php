<?php

namespace Rabble\SnippetBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use Rabble\ContentBundle\Content\Structure\StructureBuilder;
use Rabble\ContentBundle\Persistence\Manager\ContentManager;
use Rabble\SnippetBundle\Document\DefaultSnippets;
use Rabble\SnippetBundle\Document\Snippet;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RabbleSnippetExtension extends AbstractExtension
{
    private ContentManager $snippetManager;
    private StructureBuilder $structureBuilder;
    private ArrayCollection $indexes;
    private RequestStack $requestStack;
    private string $defaultLocale;

    public function __construct(
        ContentManager $snippetManager,
        StructureBuilder $structureBuilder,
        ArrayCollection $indexes,
        RequestStack $requestStack,
        string $defaultLocale
    ) {
        $this->snippetManager = $snippetManager;
        $this->structureBuilder = $structureBuilder;
        $this->indexes = $indexes;
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_snippets', [$this, 'getSnippets']),
            new TwigFunction('default_snippet', [$this, 'getDefaultSnippet']),
        ];
    }

    public function getSnippets(string $snippetType, ?string $locale = null): array
    {
        $locale = $locale ?? $this->getLocale();
        $this->snippetManager->setLocale($locale);
        $index = $this->getIndex();
        $search = $index->createSearch();
        $search->addQuery(new MatchQuery('snippetType', $snippetType));
        $results = $index->search($search->toArray());
        $snippets = [];
        foreach ($results['hits']['hits'] as $result) {
            $snippet = $this->snippetManager->find($result['_id']);
            if ($snippet instanceof Snippet) {
                $snippets[] = $this->structureBuilder->build($snippet);
            }
        }

        return $snippets;
    }

    public function getDefaultSnippet(string $snippetType, ?string $locale = null): ?array
    {
        $locale = $locale ?? $this->getLocale();
        $this->snippetManager->setLocale($locale);
        $document = $this->snippetManager->find(sprintf('%s/%s', DefaultSnippets::ROOT_NODE, DefaultSnippets::DEFAULT_NODE_NAME));
        if (!$document instanceof DefaultSnippets || !array_key_exists($snippetType, $document->getDefaults())) {
            return null;
        }
        $uuid = $document->getDefaults()[$snippetType];
        $snippet = $this->snippetManager->find($uuid);
        if ($snippet instanceof Snippet) {
            return $this->structureBuilder->build($snippet);
        }

        return null;
    }

    private function getLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return null === $request ? $this->defaultLocale : $request->getLocale();
    }

    private function getIndex(?string $locale = null): IndexService
    {
        if (null === $locale) {
            $request = $this->requestStack->getCurrentRequest();
            $locale = null === $request ? $this->defaultLocale : $request->getLocale();
        }

        return $this->indexes['snippet-'.$locale] ?? $this->indexes->first();
    }
}
