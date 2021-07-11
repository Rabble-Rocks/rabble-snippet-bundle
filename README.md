# Rabble Snippet Bundle
Snippets are small pieces of content that can be loaded and used anywhere on the website.

# Installation
Install the bundle by running
```sh
composer require rabble/snippet-bundle
```

Add the following class to your `config/bundles.php` file:
```php
return [
    ...
    Rabble\SnippetBundle\RabbleSnippetBundle::class => ['all' => true],
]
```
