<?php

namespace Knplabs\Bundle\PaginatorBundle\Templating\Helper;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zend\Paginator\Paginator;
use Knplabs\Bundle\PaginatorBundle\Paginator\Adapter;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
     * @param TranslatorInterface $translator
     */
    public function __construct(EngineInterface $engine, RouterHelper $routerHelper, TranslatorInterface $translator)
    {
        $this->engine = $engine;
        $this->routerHelper = $routerHelper;
        $this->translator = $translator;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $request = $event->getRequest();

            $this->route = $request->attributes->get('_route');
            $this->params = array_merge($request->query->all(), $request->attributes->all());
            foreach ($this->params as $key => $param) {
                if (substr($key, 0, 1) == '_') {
                    unset($this->params[$key]);
                }
            }
        }
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
        $alias = $this->getAlias($paginator);
        $options = array_merge(array(
            'absolute' => false
        ), $options);

        if (null === $route) {
            $route = $this->route;
        }
        $params = array_merge($this->params, $params);
        $direction = isset($options[$alias.'direction']) ? $options[$alias.'direction'] : 'asc';

        $sorted = isset($params[$alias.'sort']) && $params[$alias.'sort'] == $key;
        if ($sorted) {
            $direction = $params[$alias.'direction'];
            $direction = (strtolower($direction) == 'asc') ? 'desc' : 'asc';
            $class = $direction == 'asc' ? 'desc' : 'asc';
            if (isset($options['class'])) {
                $options['class'] .= ' ' . $class;
            } else {
                $options['class'] = $class;
            }
        } else {
            $options['class'] = 'sortable';
        }
        if (is_array($title) && array_key_exists($direction, $title)) {
            $title = $title[$direction];
        }
        $params = array_merge(
            $params,
            array($alias.'sort' => $key, $alias.'direction' => $direction)
        );
        return $this->buildLink($params, $route, $this->translator->trans($title), $options);
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
        if ($template) {
            $this->template = $template;
        }
        if (null === $route) {
            $route = $this->route;
        }

        $params = get_object_vars($paginator->getPages($this->scrollingStyle));
        $params['route'] = $route;
        $params['alias'] = $this->getAlias($paginator);
        $params['query'] = array_merge($this->params, $routeparams);
        $params['custom'] = $custom;

        return $this->engine->render($this->template, $params);
    }

    /**
     * Get the alias of $paginator
     *
     * @param Zend\Paginator\Paginator $paginator
     * @return string
     */
    private function getAlias(Paginator $paginator)
    {
        $alias = '';
        $adapter = $paginator->getAdapter();
        if ($adapter instanceof Adapter) {
            $alias = $adapter->getAlias();
        }

        return $alias;
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
     * @param string $route
     * @param string $title
     * @param array $options
     * @return string
     */
    private function buildLink($params, $route, $title, $options = array())
    {
        $options['href'] = $this->routerHelper->generate($route, $params, $options['absolute']);
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
