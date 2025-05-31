<?php

namespace Bitter\MinifyHtml;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router
            ->buildGroup()
            ->setNamespace('Concrete\Package\MinifyHtml\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/minify_html')
            ->routes('dialogs/support.php', 'minify_html');
    }
}