<?php

namespace Rabble\SnippetBundle\Datatable;

use Doctrine\Common\Collections\ArrayCollection;
use Rabble\AdminBundle\EventListener\RouterContextSubscriber;
use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Rabble\DatatableBundle\Datatable\DataFetcher\ElasticsearchDataFetcher;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\Action\Action;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\ActionDataColumn;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\GenericDataColumn;
use Rabble\DatatableBundle\Datatable\Row\Heading\Column\GenericHeadingColumn;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class SnippetDatatable extends AbstractGenericDatatable
{
    private RequestStack $requestStack;
    private RouterInterface $router;
    /** @var ElasticsearchDataFetcher[] */
    private array $dataFetchers;

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        ArrayCollection $dataFetchers
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->dataFetchers = $dataFetchers->toArray();
    }

    public function initialize(): void
    {
        $request = $this->requestStack->getMainRequest();
        $locale = $request->attributes->get(RouterContextSubscriber::CONTENT_LOCALE_KEY);
        $this->setConfiguration(['data_fetcher' => $this->dataFetchers[$locale] ?? current($this->dataFetchers)]);
        $this->headingColumns = [
            new GenericHeadingColumn('', false, ['style' => ['width' => 60], 'data-sortable' => 'false']),
            new GenericHeadingColumn('table.snippet.title', 'RabbleSnippetBundle'),
            new GenericHeadingColumn('table.snippet.type', 'RabbleSnippetBundle', ['data-sortable' => 'false']),
        ];
        $this->dataColumns = [
            new ActionDataColumn([
                'actions' => [
                    new Action(
                        'Routing.generate("rabble_admin_snippet_edit", {snippet: data["id"]})',
                        'pencil'
                    ),
                    new Action(
                        'Routing.generate("rabble_admin_snippet_delete", {snippet: data["id"]})',
                        'trash',
                        true,
                        [
                            'class' => 'btn-danger',
                            'data-confirm' => '?Translator.trans("snippet.delete_confirm", [], "RabbleSnippetBundle")',
                            'data-reload-datatable' => $this->getName(),
                        ]
                    ),
                ],
            ]),
            new GenericDataColumn([
                'expression' => 'data["title"]',
                'searchField' => 'title',
                'sortField' => 'title.keyword',
            ]),
            new GenericDataColumn([
                'expression' => 'data["snippetType"]',
            ]),
        ];
    }

    public function render(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->setOptions(['ajax' => $this->router->generate('rabble_datatable_table_localized', [
            'datatable' => $this->getName(),
            RouterContextSubscriber::CONTENT_LOCALE_KEY => $request->attributes->get(RouterContextSubscriber::CONTENT_LOCALE_KEY),
        ])]);

        return parent::render();
    }
}
