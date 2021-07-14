<?php

namespace Rabble\SnippetBundle\Controller;

use Rabble\AdminBundle\EventListener\RouterContextSubscriber;
use Rabble\ContentBundle\Persistence\Manager\ContentManager;
use Rabble\SnippetBundle\Document\DefaultSnippets;
use Rabble\SnippetBundle\Form\DefaultSnippetsType;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultSnippetsController extends AbstractController
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

    public function indexAction(Request $request): Response
    {
        $this->contentManager->setLocale($request->attributes->get(RouterContextSubscriber::CONTENT_LOCALE_KEY));
        $document = $this->contentManager->find(sprintf('%s/%s', DefaultSnippets::ROOT_NODE, DefaultSnippets::DEFAULT_NODE_NAME));
        if (null === $document) {
            $document = new DefaultSnippets();
        }
        $form = $this->createForm(DefaultSnippetsType::class, $document)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->contentManager->persist($document);
            $this->contentManager->flush();
        }

        return $this->render('@RabbleSnippet/DefaultSnippets/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
