<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\AbstractPagination;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SlidingPagination extends AbstractPagination
{
    private $routerHelper;
    private $route;
    private $params;
    private $translator;
    private $engine;
    private $pageRange = 5;
    private $template;
    private $sortableTemplate;
    private $paginationData;
    private $additionalViewData;

    public function __construct(EngineInterface $engine, RouterHelper $routerHelper, TranslatorInterface $translator, array $params)
    {
        $this->routerHelper = $routerHelper;
        $this->engine = $engine;
        $this->translator = $translator;
        $this->params = $params;
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

    public function setAdditionalViewData($params)
    {
        $this->additionalViewData = $params;
    }

    public function render($template = null, $queryParams = array())
    {
        if ($template) {
            $this->template = $template;
        }
        $this->getPaginationData();
        $this->paginationData['query'] = array_merge($this->params, $queryParams);
        return $this->engine->render($this->template, $this->paginationData);
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
    public function sortable($title, $key, $options = array(), $params = array(), $template = null)
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

        $options['href'] = $this->routerHelper->generate($this->route, $params, $options['absolute']);
        unset($options['absolute']);

        $title = $this->translator->trans($title);
        if (!isset($options['title'])) {
            $options['title'] = $title;
        }

        if ($template) {
            $this->sortableTemplate = $template;
        }

        return $this->engine->render($this->sortableTemplate, compact('options', 'title'));
    }

    public function getPaginationData()
    {
        $pageCount = intval(ceil($this->totalCount / $this->numItemsPerPage));
        $current = $this->currentPageNumber;

        if ($this->pageRange > $pageCount) {
            $this->pageRange = $pageCount;
        }

        $delta = ceil($this->pageRange / 2);

        if ($current - $delta > $pageCount - $this->pageRange) {
            $pages = range($pageCount - $this->pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $this->pageRange);
        }

        $this->paginationData = array(
            'last' => $pageCount,
            'current' => $current,
            'numItemsPerPage' => $this->numItemsPerPage,
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $this->totalCount,
        );

        if ($current - 1 > 0) {
            $this->paginationData['previous'] = $current - 1;
        }

        if ($current + 1 <= $pageCount) {
            $this->paginationData['next'] = $current + 1;
        }
        $this->paginationData['pagesInRange'] = $pages;
        $this->paginationData['firstPageInRange'] = min($pages);
        $this->paginationData['lastPageInRange']  = max($pages);

        if ($this->getItems() !== null) {
            $this->paginationData['currentItemCount'] = $this->count();
            $this->paginationData['firstItemNumber'] = (($current - 1) * $this->numItemsPerPage) + 1;
            $this->paginationData['lastItemNumber'] = $this->paginationData['firstItemNumber'] + $this->paginationData['currentItemCount'] - 1;
        }

        $this->paginationData['route'] = $this->route;
        $this->paginationData['alias'] = $this->alias;

        $this->paginationData = array_merge($this->paginationData, $this->additionalViewData);

        return $this->paginationData;
    }

    public function getFirstItemNumber()
    {
        return $this->paginationData['firstItemNumber'];
    }

}