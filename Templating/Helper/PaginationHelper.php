<?php

namespace Bundle\DoctrinePaginatorBundle\Templating\Helper;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\Engine;
use Zend\Paginator\Paginator;

/**
 * Pagination view helper
 * Responsible for rendering pagination control
 * also sorting the inner fields
 */
class PaginationHelper extends Helper
{
    /**
     * Router helper for url generation
     * 
     * @var RouterHelper
     */
    protected $routerHelper;
    
    /**
     * Current request object
     * to retrieve url query parameters
     * 
     * @var Request
     */
    protected $request;
    
    /**
     * Template rendering engine
     * used for pagination control
     * rendering
     * 
     * @var Engine
     */
    protected $engine;
    
    /**
     * Currently matched route
     * 
     * @var string
     */
    private $route;
    
    /**
     * Request query parameters
     * 
     * @var array
     */
    private $params;
    
    /**
     * Scrolling style for Zend paginator
     * to create related pagination control
     * 
     * @var string
     */
    private $scrollingStyle;
    
    /**
     * Pagination control template
     * 
     * @var string
     */
    private $template;
    
    /**
     * Initialize pagination helper
     * 
     * @param Engine $engine
     * @param RouterHelper $routerHelper
     * @param Request $request
     * @param array $options
     */
    public function __construct(Engine $engine, RouterHelper $routerHelper, Request $request, array $options = array())
    {
        $this->scrollingStyle = $options['style'];
        $this->template = $options['template'];
        $this->engine = $engine;
        $this->request = $request;
        $this->routerHelper = $routerHelper;
        
        $this->route = $this->request->get('_route');
        $this->params = $this->request->query->all();
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
     * @return string
     */
    public function sort($title, $key, $options = array())
    {
        $options = array_merge(array(
    		'absolute' => false
    	), $options);
        
        $params = $this->params;
        $direction = isset($options['direction']) ? $options['direction'] : 'asc';
    	
        $sorted = isset($params['sort']) && $params['sort'] == $key;
        if ($sorted) {
    		$direction = $params['direction'];
            $direction = (strtolower($direction) == 'asc') ? 'desc' : 'asc';
            $class = $direction == 'asc' ? 'desc' : 'asc';
    		if (isset($options['class'])) {
				$options['class'] .= ' ' . $class;
			} else {
				$options['class'] = $class;
			}
    	} else {
    	    $options['class'] = 'sort';
    	}
        if (is_array($title) && array_key_exists($direction, $title)) {
			$title = $title[$direction];
		}
		$params = array_merge(
			$params,
			array('sort' => $key, 'direction' => $direction)
		);
        return $this->buildLink($params, $title, $options);
    }
    
    /**
     * Renders a pagination control, for a $paginator given.
     * Optionaly $template and $style can be specified to
     * override default from configuration.
     * 
     * @param Zend\Paginator\Paginator $paginator
     * @param string $template
     * @param string $style
     * @return string
     */
    public function render(Paginator $paginator, $template = null, $style = null)
    {
        if ($style) {
            $this->scrollingStyle = $style;
        }
        if ($template) {
            $this->template = $template;
        }
        $params = get_object_vars($paginator->getPages($this->scrollingStyle));
        $params['route'] = $this->route;
        $params['query'] = $this->params;
        return $this->engine->render($this->template, $params);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'pagination';
    }

    /**
     * Build a HTML link. $options holds all link parameters
     * like "alt, class" and so on. $title can also be an image
     * if required.
     * 
     * @param array $params - url query params
     * @param string $title
     * @param array $options
     * @return string
     */
    private function buildLink($params, $title, $options = array())
    {
        $options['href'] = $this->routerHelper->generate($this->route, $params, $options['absolute']);
        unset($options['absolute']);
        
        if (!isset($options['title'])) {
            $options['title'] = $title;
        }
        $link = '<a';
        foreach ($options as $attr => $value) {
            $link .= ' ' . $attr . '="' . $value . '"';
        }
        $link .= '>' . $title . '</a>';
        return $link;
    }
}