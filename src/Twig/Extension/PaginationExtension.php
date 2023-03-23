<?php

namespace Knp\Bundle\PaginatorBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PaginationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        $options = ['is_safe' => ['html'], 'needs_environment' => true];

        return [
            new TwigFunction('knp_pagination_render', [PaginationRuntime::class, 'render'], $options),
            new TwigFunction('knp_pagination_sortable', [PaginationRuntime::class, 'sortable'], $options),
            new TwigFunction('knp_pagination_filter', [PaginationRuntime::class, 'filter'], $options),
        ];
    }
}
