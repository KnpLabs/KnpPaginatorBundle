<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Knp\Bundle\PaginatorBundle\Templating\PaginationHelper;

return static function (ContainerConfigurator $configurator): void {
    $configurator
        ->parameters()
        ->set('knp_paginator.templating.helper.pagination.class', PaginationHelper::class)
    ;

    $configurator
        ->services()
        ->set('knp_paginator.templating.helper.pagination', param('knp_paginator.templating.helper.pagination.class'))
        ->arg(0, service('knp_paginator.helper.processor'))
        ->arg(1, service('templating.engine.php'))
        ->tag('templating.helper', ['alias' => 'knp_pagination'])
    ;
};
