<?php

namespace Knp\Bundle\PaginatorBundle\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\Translation\TranslatorInterface;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    public function __construct(RouterHelper $routerHelper, TranslatorInterface $translator)
    {
        $this->routerHelper = $routerHelper;
        $this->translator = $translator;
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
        $template = $template ?: $pagination->getTemplate();

        $data = $pagination->getPaginationData();

        $data['route'] = $pagination->getRoute();
        $data['query'] = array_merge($pagination->getParams(), $queryParams);

        $data = array_merge(
            $pagination->getPaginatorOptions(), // options given to paginator when paginated
            $pagination->getCustomParameters(), // all custom parameters for view
            $viewParams, // additional custom parameters for view
            $data // merging base route parameters last, to avoid broke of integrity
        );

        return $this->environment->render($template, $data);
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
        $options = array_merge(array(
            'absolute' => false,
            'translationParameters' => array(),
            'translationDomain' => null,
            'translationCount' => null,
        ), $options);

        $params = array_merge($pagination->getParams(), $params);

        $direction = isset($options[$pagination->getPaginatorOption('sortDirectionParameterName')])
            ? $options[$pagination->getPaginatorOption('sortDirectionParameterName')]
            : (isset($options['defaultDirection']) ? $options['defaultDirection'] : 'asc')
        ;

        $sorted = $pagination->isSorted($key, $params);

        if ($sorted) {
            $direction = $params[$pagination->getPaginatorOption('sortDirectionParameterName')];
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
                $pagination->getPaginatorOption('sortFieldParameterName') => $key,
                $pagination->getPaginatorOption('sortDirectionParameterName') => $direction,
                $pagination->getPaginatorOption('pageParameterName') => 1 // reset to 1 on sort
            )
        );

        $options['href'] = $this->routerHelper->generate($pagination->getRoute(), $params, $options['absolute']);

        if (null !== $options['translationDomain']) {
            if (null !== $options['translationCount']) {
                $title = $this->translator->transChoice($title, $options['translationCount'], $options['translationParameters'], $options['translationDomain']);
            } else {
                $title = $this->translator->trans($title, $options['translationParameters'], $options['translationDomain']);
            }
        }

        if (!isset($options['title'])) {
            $options['title'] = $title;
        }

        $template = $template ?: $pagination->getSortableTemplate();

        unset($options['absolute'], $options['translationDomain'], $options['translationParameters']);

        return $this->environment->render($template, array_merge(
            $pagination->getPaginatorOptions(),
            $pagination->getCustomParameters(),
            compact('options', 'title', 'direction', 'sorted', 'key')
        ));
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
        $options = array_merge(array(
            'absolute' => false,
            'translationParameters' => array(),
            'translationDomain' => null,
            'button' => 'Filter',
        ), $options);

        $params = array_merge($pagination->getParams(), $params);
        $params[$pagination->getPaginatorOption('pageParameterName')] = 1; // reset to 1 on filter

        $filterFieldName = $pagination->getPaginatorOption('filterFieldParameterName');
        $filterValueName = $pagination->getPaginatorOption('filterValueParameterName');

        $selectedField = isset($params[$filterFieldName]) ? $params[$filterFieldName] : null;
        $selectedValue = isset($params[$filterValueName]) ? $params[$filterValueName] : null;

        $action = $this->routerHelper->generate($pagination->getRoute(), $params, $options['absolute']);

        foreach ($fields as $field => $title) {
            $fields[$field] = $this->translator->trans($title, $options['translationParameters'], $options['translationDomain']);
        }
        $options['button'] = $this->translator->trans($options['button'], $options['translationParameters'], $options['translationDomain']);

        $template = $template ?: $pagination->getFiltrationTemplate();

        unset($options['absolute'], $options['translationDomain'], $options['translationParameters']);

        return $this->environment->render($template, array_merge(
            $pagination->getPaginatorOptions(),
            $pagination->getCustomParameters(),
            compact('fields', 'action', 'filterFieldName', 'filterValueName', 'selectedField', 'selectedValue', 'options')
        ));
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
