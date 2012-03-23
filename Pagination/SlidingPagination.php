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
    private $extraViewParams = array();

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

    /**
     * Renders the pagination template
     *
     * @param string $template
     * @param array $queryParams
     * @param array $viewParams
     * @return string
     */
    public function render($template = null, array $queryParams = array(), array $viewParams = array())
    {
        if ($template) {
            $this->template = $template;
        }
        $data = $this->getPaginationData();
        $data['route'] = $this->route;
        $data['query'] = array_merge($this->params, $queryParams);
        $data = array_merge(
            $this->paginatorOptions, // options given to paginator when paginated
            $this->customParameters, // all custom parameters for view
            $viewParams, // additional custom parameters for view
            $data // merging base route parameters last, to avoid broke of integrity
        );
        return $this->engine->render($this->template, $data);
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
        $direction = isset($options[$this->getPaginatorOption('sortDirectionParameterName')]) ?
            $options[$this->getPaginatorOption('sortDirectionParameterName')] : 'asc'
        ;

        $sorted = isset($params[$this->getPaginatorOption('sortFieldParameterName')])
            && $params[$this->getPaginatorOption('sortFieldParameterName')] == $key
        ;
        if ($sorted) {
            $direction = $params[$this->getPaginatorOption('sortDirectionParameterName')];
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
            array(
                $this->getPaginatorOption('sortFieldParameterName') => $key,
                $this->getPaginatorOption('sortDirectionParameterName') => $direction,
                $this->getPaginatorOption('pageParameterName') => 1 // reset to 1 on sort
            )
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

        return $this->engine->render($this->sortableTemplate, array_merge(
            $this->paginatorOptions,
            $this->customParameters,
            compact('options', 'title', 'direction', 'sorted', 'key')
        ));
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

        $viewData = array(
            'last' => $pageCount,
            'current' => $current,
            'numItemsPerPage' => $this->numItemsPerPage,
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $this->totalCount,
            'pageRange' => $this->pageRange,
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
