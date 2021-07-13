<?php

namespace Rabble\SnippetBundle\Form;

use Jackalope\Node;
use Jackalope\Session;
use PHPCR\RepositoryException;
use Rabble\ContentBundle\Persistence\Manager\ContentManager;
use Rabble\SnippetBundle\Document\Snippet;
use Rabble\SnippetBundle\SnippetType\Manager\SnippetTypeManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultSnippetsType extends AbstractType
{
    private Session $session;
    private ContentManager $snippetManager;
    private SnippetTypeManagerInterface $snippetTypeManager;
    private TranslatorInterface $translator;

    public function __construct(
        Session $session,
        ContentManager $snippetManager,
        SnippetTypeManagerInterface $snippetTypeManager,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->snippetManager = $snippetManager;
        $this->snippetTypeManager = $snippetTypeManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaults = $builder->create('defaults', FormType::class, [
            'label' => false,
        ]);
        $groupedSnippets = [];

        try {
            $snippetsNode = $this->session->getNode(Snippet::ROOT_NODE);
            /** @var Node $snippetNode */
            foreach ($snippetsNode->getNodes() as $snippetNode) {
                $snippet = $this->snippetManager->find($snippetNode->getPath());
                if (!$snippet instanceof Snippet) {
                    continue;
                }
                if (!isset($groupedSnippets[$snippet->getSnippetType()])) {
                    $groupedSnippets[$snippet->getSnippetType()] = [];
                }
                $groupedSnippets[$snippet->getSnippetType()][] = $snippet;
            }
            foreach ($groupedSnippets as $type => $snippets) {
                if (!$this->snippetTypeManager->has($type)) {
                    continue;
                }
                $snippetType = $this->snippetTypeManager->get($type);
                $choices = [];
                foreach ($snippets as $snippet) {
                    $label = $snippet->getTitle();
                    if (isset($choices[$label])) {
                        $label = $snippet->getTitle().' ('.$snippet->getNodeName().')';
                    }
                    $choices[$label] = $snippet->getUuid();
                }
                $options = [
                    'required' => false,
                    'choices' => $choices,
                    'choice_translation_domain' => false,
                    'translation_domain' => false,
                    'label' => $snippetType->getName(),
                ];
                if ($snippetType->hasAttribute('label_'.$this->translator->getLocale())) {
                    $options['label'] = $snippetType->getAttribute('label_'.$this->translator->getLocale());
                } elseif ($snippetType->hasAttribute('translation_domain')) {
                    $options['label'] = 'snippet_type.'.$snippetType->getName();
                    $options['translation_domain'] = $snippetType->getAttribute('translation_domain');
                }

                $defaults->add($snippetType->getName(), ChoiceType::class, $options);
            }
        } catch (RepositoryException $exception) {
        }

        $builder->add($defaults);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Save',
        ]);
    }
}
