<?php

namespace Rabble\SnippetBundle\Menu;

use Rabble\AdminBundle\Menu\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getRootItem();
        $menu->addChild('rabble_snippet_index', [
            'label' => 'menu.snippet.index',
            'route' => 'rabble_admin_snippet_index',
            'extras' => [
                'translation_domain' => 'RabbleSnippetBundle',
                'icon' => 'ti-notepad',
                'icon_color' => 'red-400',
                'routes' => ['rabble_admin_snippet_create', 'rabble_admin_snippet_edit'],
            ],
        ]);
    }
}
