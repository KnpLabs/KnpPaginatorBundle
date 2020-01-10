<?php

namespace Knp\Bundle\PaginatorBundle\Templating;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\PhpEngine;

/**
 * Pagination PHP helper.
 *
 * Basically provides access to KnpPaginator from PHP templates
 *
 * @author Rafa³ Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 */
final class PaginationHelper extends Helper
{
    /**
     * @var PhpEngine
     */
    protected $templating;

    /**
     * @var Processor
     */
    protected $processor;

    public function __construct(Processor $processor, PhpEngine $templating)
    {
        $this->processor = $processor;
        $this->templating = $templating;
    }

    /**
     * Renders the pagination template.
     *
     * @param string $template
     */
    public function render(SlidingPaginationInterface $pagination, ?string $template = null, array $queryParams = [], array $viewParams = []): string
    {
        return $this->templating->render(
            $template ?: $pagination->getTemplate(),
            $this->processor->render($pagination, $queryParams, $viewParams)
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
     * @param string|array $key
     * @param string       $template
     */
    public function sortable(SlidingPaginationInterface $pagination, string $title, $key, array $options = [], array $params = [], ?string $template = null): string
    {
        return $this->templating->render(
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
     * @param string $template
     */
    public function filter(SlidingPaginationInterface $pagination, array $fields, array $options = [], array $params = [], ?string $template = null): string
    {
        return $this->templating->render(
            $template ?: $pagination->getFiltrationTemplate(),
            $this->processor->filter($pagination, $fields, $options, $params)
        );
    }

    /**
     * Get helper name.
     */
    public function getName(): string
    {
        return 'knp_pagination';
    }
}
