<?php

namespace Knplabs\PaginatorBundle\Templating\Helper;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
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
     * @var DelegatingEngine
     */
    protected $engine;
    
    /**
     * Translator
     * 
     * @var TranslatorInterface
     */
    protected $translator;
    
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
     * @param EngineInterface $engine
     * @param RouterHelper $routerHelper
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param array $options
     */
    public function __construct(EngineInterface $engine, RouterHelper $routerHelper, Request $request, TranslatorInterface $translator)
    {
        $this->engine = $engine;
        $this->request = $request;
        $this->routerHelper = $routerHelper;
        $this->translator = $translator;
        
        $this->route = $this->request->get('_route');
        $this->params = $this->request->query->all();
    }
    
    /**
     * Set the template to render for pagination
     * 
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    /**
     * Set the style for pagination
     * 
     * @param string $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
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
    public function sort($title, $key, $options = array(), $params = array())
    {
        $options = array_merge(array(
            'absolute' => false
        ), $options);
        
        $params = array_merge($this->params,$params);
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
        return $this->buildLink($params, $this->translator->trans($title), $options);
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
    public function render(Paginator $paginator, $template = null, $custom = array(), $routeparams=array())
    {
        if ($template) {
            $this->template = $template;
        }
        $params = get_object_vars($paginator->getPages($this->scrollingStyle));
        $params['route'] = $this->route;
        $params['query'] = array_merge($this->params,$routeparams);
        $params['custom'] = $custom;
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