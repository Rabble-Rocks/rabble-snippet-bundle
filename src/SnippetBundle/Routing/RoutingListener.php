<?php

namespace Rabble\SnippetBundle\Routing;

use Rabble\AdminBundle\Routing\Event\RoutingEvent;

class RoutingListener
{
    public function onRoutingLoad(RoutingEvent $event)
    {
        $event->addResources('xml', ['@RabbleSnippetBundle/Resources/config/routing.xml']);
    }
}
