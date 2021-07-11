<?php

namespace Rabble\SnippetBundle\Controller;

use Rabble\AdminBundle\EventListener\RouterContextSubscriber;
use Rabble\ContentBundle\Form\ContentFormType;
use Rabble\ContentBundle\Persistence\Manager\ContentManager;
use Rabble\SnippetBundle\Document\Snippet;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SnippetController extends AbstractController
{
    private SnippetTypeManagerInterface $snippetTypeManager;
    private ContentManager $contentManager;

    public function __construct(
        SnippetTypeManagerInterface $snippetTypeManager,
        ContentManager $contentManager
    ) {
        $this->snippetTypeManager = $snippetTypeManager;
        $this->contentManager = $contentManager;
    }

    public function indexAction(): Response
    {
        return $this->render('@RabbleSnippet/Snippet/index.html.twig', [
            'snippet_types' => $this->snippetTypeManager->all(),
        ]);
    }

    public function createAction(Request $request, $snippetType): Response
    {
        $this->contentManager->setLocale($request->attributes->get(RouterContextSubscriber::CONTENT_LOCALE_KEY));
        $snippetType = $this->snippetTypeManager->get($snippetType);
        $snippet = new Snippet();
        $snippet->setSnippetType($snippetType->getName());
        $form = $this->createForm(
            ContentFormType::class,
            $snippet,
            [
                'fields' => $snippetType->getFields(),
            ]
        )->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->contentManager->persist($snippet);
            $this->contentManager->flush();
            $this->addFlash('success', 'The snippet has been saved.');
        }

        return $this->render($snippetType->getAttribute('template') ?? '@RabbleSnippet/Snippet/form.html.twig', [
            'form' => $form->createView(),
            'snippet' => $snippet,
            'action' => 'create',
            'snippetType' => $snippetType,
        ]);
    }

    public function editAction(Request $request, $snippet): Response
    {
        $this->contentManager->setLocale($request->attributes->get(RouterContextSubscriber::CONTENT_LOCALE_KEY));
        $snippet = $this->contentManager->find($snippet);
        if (!$snippet instanceof Snippet) {
            throw new NotFoundHttpException();
        }
        $snippetType = $this->snippetTypeManager->get($snippet->getSnippetType());
        $form = $this->createForm(
            ContentFormType::class,
            $snippet,
            ['fields' => $snippetType->getFields()]
        )->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->contentManager->flush();
            $this->addFlash('success', 'The snippet has been saved.');
            $form = $this->createForm(
                ContentFormType::class,
                $snippet,
                ['fields' => $snippetType->getFields()]
            );
        }

        return $this->render($snippetType->getAttribute('template') ?? '@RabbleSnippet/Snippet/form.html.twig', [
            'form' => $form->createView(),
            'snippet' => $snippet,
            'action' => 'edit',
            'snippetType' => $snippetType,
        ]);
    }

    public function deleteAction($snippet): RedirectResponse
    {
        $snippet = $this->contentManager->find($snippet);
        if (null === $snippet) {
            throw new NotFoundHttpException();
        }
        $this->contentManager->remove($snippet);
        $this->contentManager->flush();

        return $this->redirectToRoute('rabble_admin_snippet_index');
    }
}
