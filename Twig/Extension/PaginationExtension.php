<?php

namespace Knp\Bundle\PaginatorBundle\Twig\Extension;

use Knp\Bundle\PaginatorBundle\Helper\Processor;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

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
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'knp_pagination_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
            'knp_pagination_sortable' => new \Twig_Function_Method($this, 'sortable', array('is_safe' => array('html'))),
            'knp_pagination_filter' => new \Twig_Function_Method($this, 'filter', array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders the pagination template
     *
     * @param string $template
     * @param array $queryParams
     * @param array $viewParams
     *
     * @return string
     */
    public function render($pagination, $template = null, array $queryParams = array(), array $viewParams = array())
    {
        return $this->environment->render(
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
     * @param string $title
     * @param string $key
     * @param array $options
     * @param array $params
     * @param string $template
     * @return string
     */
    public function sortable($pagination, $title, $key, $options = array(), $params = array(), $template = null)
    {
        return $this->environment->render(
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
     * @param string $title
     * @param string $key
     * @param array $options
     * @param array $params
     * @param string $template
     * @return string
     */
    public function filter($pagination, array $fields, $options = array(), $params = array(), $template = null)
    {
        return $this->environment->render(
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
