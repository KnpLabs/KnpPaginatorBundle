<?php

namespace Knp\Bundle\PaginatorBundle\Twig\Extension;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var Processor
     */
    protected $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('knp_pagination_render', array($this, 'render'), array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('knp_pagination_sortable', array($this, 'sortable'), array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('knp_pagination_filter', array($this, 'filter'), array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    /**
     * Renders the pagination template
     *
     * @param \Twig_Environment $env
     * @param SlidingPagination $pagination
     * @param string            $template
     * @param array             $queryParams
     * @param array             $viewParams
     *
     * @return string
     */
    public function render(\Twig_Environment $env, SlidingPagination $pagination, $template = null, array $queryParams = array(), array $viewParams = array())
    {
        return $env->render(
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
     * @param \Twig_Environment $env
     * @param SlidingPagination $pagination
     * @param string            $title
     * @param string            $key
     * @param array             $options
     * @param array             $params
     * @param string            $template
     *
     * @return string
     */
    public function sortable(\Twig_Environment $env, SlidingPagination $pagination, $title, $key, $options = array(), $params = array(), $template = null)
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
     * @param \Twig_Environment $env
     * @param SlidingPagination $pagination
     * @param array             $fields
     * @param array             $options
     * @param array             $params
     * @param string            $template
     *
     * @return string
     */
    public function filter(\Twig_Environment $env, SlidingPagination $pagination, array $fields, $options = array(), $params = array(), $template = null)
    {
        return $env->render(
            $template ?: $pagination->getFiltrationTemplate(),
            $this->processor->filter($pagination, $fields, $options, $params)
        );
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'knp_pagination';
    }
}
