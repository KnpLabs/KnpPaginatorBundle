<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\AbstractPagination;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use IteratorAggregate, Countable, Traversable, ArrayIterator;

class SlidingPagination extends AbstractPagination implements Countable, IteratorAggregate
{
    private $routerHelper;
    private $route;
    private $params;
    private $translator;
    private $engine;
    private $pageRange = 5;
    private $template;
    private $sortableTemplate;

    public function __construct(EngineInterface $engine, RouterHelper $routerHelper, TranslatorInterface $translator, array $params)
    {
        $this->routerHelper = $routerHelper;
        $this->engine = $engine;
        $this->translator = $translator;
    }

    public function setUsedRoute($route)
    {
        $this->route = $route;
    }

    public function setSortableTemplate($template)
    {
        $this->sortableTemplate = $template;
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setPageRange($range)
    {
        $this->pageRange = abs(intval($range));
    }

    /**
     * Returns a foreach-compatible iterator.
     *
     * @return Traversable
     */
    public function getIterator()
    {
        $items = $this->getItems();
        if (!$items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }
        return $items;
    }

    public function count()
    {
        return count($this->items);
    }

    /**
     * Renders the pagination
     */
    public function __toString()
    {
        $data = $this->getPaginationData();
        $data['route'] = $this->route;
        $data['alias'] = $this->alias;
        return $this->engine->render($this->engine, $data);
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
    * @param string $route
    * @return string
    */
    public function sortable($title, $key, $options = array(), $params = array())
    {
        $options = array_merge(array(
            'absolute' => false
        ), $options);

        $params = array_merge($this->params, $params);
        $direction = isset($options[$this->alias.'direction']) ? $options[$this->alias.'direction'] : 'asc';

        $sorted = isset($params[$this->alias.'sort']) && $params[$this->alias.'sort'] == $key;
        if ($sorted) {
            $direction = $params[$this->alias.'direction'];
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
            array($this->alias.'sort' => $key, $this->alias.'direction' => $direction)
        );
        return $this->buildLink($params, $this->translator->trans($title), $options);
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

        return $this->engine->render($this->sortableTemplate, compact('options', 'title'));
    }

    private function getPaginationData()
    {
        $pageCount = intval(ceil($this->totalCount / $this->numItemsPerPage));
        $current = $this->currentPageNumber;

        if ($this->range > $pageCount) {
            $this->range = $pageCount;
        }

        $delta = ceil($this->range / 2);

        if ($current - $delta > $pageCount - $this->range) {
            $pages = range($pageCount - $this->range + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $this->range);
        }

        $viewData = array(
                'last' => $pageCount,
                'current' => $current,
                'numItemsPerPage' => $this->numItemsPerPage,
                'first' => 1,
                'pageCount' => $pageCount,
                'totalCount' => $this->totalCount,
        );

        if ($current - 1 > 0) {
            $viewData['previous'] = $current - 1;
        }

        if ($current + 1 <= $pageCount) {
            $viewData['next'] = $current + 1;
        }
        $viewData['pagesInRange'] = $pages;
        $viewData['firstPageInRange'] = min($pages);
        $viewData['lastPageInRange']  = max($pages);

        if ($this->getItems() !== null) {
            $viewData['currentItemCount'] = $this->count();
            $viewData['firstItemNumber'] = (($current - 1) * $this->numItemsPerPage) + 1;
            $viewData['lastItemNumber'] = $viewData['firstItemNumber'] + $viewData['currentItemCount'] - 1;
        }

        return $viewData;
    }
}