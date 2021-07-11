<?php

namespace Rabble\SnippetBundle\Indexer;

use Rabble\ContentBundle\Content\ContentIndexer;

class SnippetIndexer extends ContentIndexer
{
    protected static function getIndexName(string $locale): string
    {
        return 'snippet-'.$locale;
    }
}
