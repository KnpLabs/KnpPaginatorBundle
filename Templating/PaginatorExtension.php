<?php

namespace Knplabs\Bundle\PaginatorBundle\Templating;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Paginator\Paginator;

class PaginatorExtension extends \Twig_Extension
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Initialize pagination helper
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'sortable' => new \Twig_Filter_Method($this, 'sortable', array('is_safe' => array('html'))),
            'paginate' => new \Twig_Filter_method($this, 'paginate', array('is_safe' => array('html')))
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
     * @param Zend\Paginator\Paginator $paginator
     * @param string $title
     * @param string $key
     * @param array $options
     * @param array $params
     * @param string $route
     * @return string
     */
    public function sortable(Paginator $paginator, $title, $key, $options = array(), $params = array(), $route = null)
    {
        return $this->container->get('templating.helper.knplabs_paginator')->sortable($paginator, $title, $key, $options, $params, $route);
    }

    /**
     * Renders a pagination control, for a $paginator given.
     * Optionaly $template and $style can be specified to
     * override default from configuration.
     *
     * @param Zend\Paginator\Paginator $paginator
     * @param string $template
     * @param array $custom - custom parameters
     * @param array $routeparams - params for the route
     * @param string $route
     * @return string
     */
    public function paginate(Paginator $paginator, $template = null, $custom = array(), $routeparams = array(), $route = null)
    {
        return $this->container->get('templating.helper.knplabs_paginator')->paginate($paginator, $template, $custom, $routeparams, $route);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'paginator';
    }
}
