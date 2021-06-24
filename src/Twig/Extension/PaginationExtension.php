<?php

namespace Knp\Bundle\PaginatorBundle\Twig\Extension;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PaginationExtension extends AbstractExtension
{
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('knp_pagination_render', [$this, 'render'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('knp_pagination_sortable', [$this, 'sortable'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('knp_pagination_filter', [$this, 'filter'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * Renders the pagination template.
     *
     * @param \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination<mixed> $pagination
     * @param array<string, mixed>                                            $queryParams
     * @param array<string, mixed>                                            $viewParams
     */
    public function render(Environment $env, SlidingPaginationInterface $pagination, ?string $template = null, ?array $queryParams = [], ?array $viewParams = []): string
    {
        return $env->render(
            $template ?: $pagination->getTemplate(),
            $this->processor->render($pagination, $queryParams ?? [], $viewParams ?? [])
        );
    }

    /**
     * Create a sort url for the field named $title
     * and identified by $key which consists of
     * alias and field. $options holds all link
     * parameters like "alt, class" and so on.
     *
     * $key example: "article.title"
     *
     * @param \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination<mixed> $pagination
     * @param string|array<string, mixed>                                     $key
     * @param array<string, mixed>                                            $options
     * @param array<string, mixed>                                            $params
     */
    public function sortable(Environment $env, SlidingPaginationInterface $pagination, string $title, $key, array $options = [], array $params = [], ?string $template = null): string
    {
        return $env->render(
            $template ?: $pagination->getSortableTemplate(),
            $this->processor->sortable($pagination, $title, $key, $options, $params)
        );
    }

    /**
     * Create a filter url for the field named $title
     * and identified by $key which consists of
     * alias and field. $options holds all link
     * parameters like "alt, class" and so on.
     *
     * $key example: "article.title"
     *
     * @param \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination<mixed> $pagination
     * @param array<string, mixed>                                            $fields
     * @param array<string, mixed>                                            $options
     * @param array<string, mixed>|null                                       $params
     */
    public function filter(Environment $env, SlidingPaginationInterface $pagination, array $fields, ?array $options = [], ?array $params = [], ?string $template = null): string
    {
        return $env->render(
            $template ?: $pagination->getFiltrationTemplate(),
            $this->processor->filter($pagination, $fields, $options ?? [], $params ?? [])
        );
    }
}
