<?php

namespace Bundle\DoctrinePaginatorBundle\Templating\Helper;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\Engine;
use Zend\Paginator\Paginator;

class PaginationHelper extends Helper
{
    protected $routerHelper;
    protected $request;
    protected $engine;
    
    private $route;
    private $params;
    private $scrollingStyle;
    private $template;
    
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
    
    public function sort($title, $key = null, $options = array())
    {
        $options = array_merge(array(
    		'absolute' => false
    	), $options);
        
        if (!$key) {
    		$key = $title;
    		$title = ucwords($key);
    	}
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
        return $this->engine->render($this->template, $params);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'pagination';
    }

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