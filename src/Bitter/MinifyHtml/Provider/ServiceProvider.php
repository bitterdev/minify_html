<?php

namespace Bitter\MinifyHtml\Provider;

use Bitter\MinifyHtml\Listener\PageOutput;
use Bitter\MinifyHtml\RouteList;
use Concrete\Core\Application\Application;
use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ServiceProvider extends Provider
{
    protected EventDispatcherInterface $eventDispatcher;
    protected RouterInterface $router;

    public function __construct(
        Application              $app,
        EventDispatcherInterface $eventDispatcher,
        RouterInterface          $router
    )
    {
        parent::__construct($app);

        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    public function register()
    {
        $this->initializeRoutes();
        $this->bindEventHandlers();
    }

    private function bindEventHandlers()
    {
        $this->eventDispatcher->addListener('on_page_output', function ($event) {
            /** @var PageOutput $listener */
            $listener = $this->app->make(PageOutput::class);
            $listener->handle($event);
        });
    }

    private function initializeRoutes()
    {
        $list = new RouteList();
        $list->loadRoutes($this->router);
    }
}