<?php

namespace Rabble\SnippetBundle\Command;

use Jackalope\Node;
use Jackalope\Session;
use Rabble\ContentBundle\Persistence\Manager\ContentManager;
use Rabble\SnippetBundle\Indexer\SnippetIndexer;
use Rabble\SnippetBundle\Persistence\SnippetPathProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnippetIndexCommand extends Command
{
    protected static $defaultName = 'rabble:snippet:index';

    private Session $session;
    private ContentManager $contentManager;
    private SnippetIndexer $snippetIndexer;
    private string $defaultLocale;

    public function __construct(
        Session $session,
        ContentManager $contentManager,
        SnippetIndexer $snippetIndexer,
        string $defaultLocale
    ) {
        $this->session = $session;
        $this->contentManager = $contentManager;
        $this->snippetIndexer = $snippetIndexer;
        $this->defaultLocale = $defaultLocale;

        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('locale', InputArgument::OPTIONAL, 'Locale to index documents for', $this->defaultLocale);
        $this->addOption('full-reset', null, InputOption::VALUE_NONE, 'Perform a full reset, clearing the entire index beforehand.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->contentManager->setLocale($input->getArgument('locale'));
        if ($input->getOption('full-reset')) {
            $this->snippetIndexer->reset();
        }
        $node = $this->session->getNode(SnippetPathProvider::ROOT_NODE);
        /** @var Node $snippet */
        foreach ($node->getNodes() as $snippet) {
            $this->snippetIndexer->index($this->contentManager->find($snippet->getPath()));
        }
        $this->snippetIndexer->commit();

        return 0;
    }
}
