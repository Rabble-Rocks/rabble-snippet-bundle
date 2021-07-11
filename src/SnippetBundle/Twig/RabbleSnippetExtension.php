<?php

namespace Rabble\SnippetBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use Rabble\ContentBundle\Content\Structure\StructureBuilder;
use Rabble\ContentBundle\Persistence\Manager\ContentManager;
use Rabble\SnippetBundle\Document\DefaultSnippets;
use Rabble\SnippetBundle\Document\Snippet;
use Rabble\SnippetBundle\Persistence\DefaultSnippetsPathProvider;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RabbleSnippetExtension extends AbstractExtension
{
    private SnippetTypeManagerInterface $snippetTypeManager;
    private ContentManager $snippetManager;
    private ContentManager $snippetDefaultsManager;
    private StructureBuilder $structureBuilder;
    private ArrayCollection $indexes;
    private RequestStack $requestStack;
    private string $defaultLocale;

    public function __construct(
        SnippetTypeManagerInterface $snippetTypeManager,
        ContentManager $snippetManager,
        ContentManager $snippetDefaultsManager,
        StructureBuilder $structureBuilder,
        ArrayCollection $indexes,
        RequestStack $requestStack,
        string $defaultLocale
    ) {
        $this->snippetTypeManager = $snippetTypeManager;
        $this->snippetManager = $snippetManager;
        $this->snippetDefaultsManager = $snippetDefaultsManager;
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
        $document = $this->snippetDefaultsManager->find(sprintf('%s/%s', DefaultSnippetsPathProvider::ROOT_NODE, DefaultSnippets::DEFAULT_NODE_NAME));
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
