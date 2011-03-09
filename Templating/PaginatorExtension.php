<?php

namespace Knplabs\PaginatorBundle\Templating;

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
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'paginator_sort'   => new \Twig_Function_Method($this, 'sort'),
            'paginator_render'  => new \Twig_Function_Method($this, 'render')
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
     * @return string
     */
    public function sort($title, $key, $options = array(), $params = array())
    {
        return $this->container->get('templating.helper.knplabs_paginator')->sort($title, $key, $options, $params);
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
     * @return string
     */
    public function render(Paginator $paginator, $template = null, $custom = array(), $routeparams = array())
    {
        return $this->container->get('templating.helper.knplabs_paginator')->render($paginator, $template, $custom, $routeparams);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'paginator';
    }
}