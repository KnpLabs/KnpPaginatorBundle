<?php

namespace Knp\Bundle\PaginatorBundle\Helper;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPaginationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Pagination data processor.
 *
 * Common data processor for all templating engines
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 */
final class Processor
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(UrlGeneratorInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Generates pagination template data.
     *
     * @param SlidingPaginationInterface<mixed> $pagination
     * @param array<string, mixed>              $queryParams
     * @param array<string, mixed>              $viewParams
     *
     * @return array<string, mixed>
     */
    public function render(SlidingPaginationInterface $pagination, array $queryParams = [], array $viewParams = []): array
    {
        $data = $pagination->getPaginationData();

        $data['route'] = $pagination->getRoute();
        $data['query'] = \array_merge($pagination->getParams(), $queryParams);

        return \array_merge(
            $pagination->getPaginatorOptions() ?? [], // options given to paginator when paginated
            $pagination->getCustomParameters() ?? [], // all custom parameters for view
            $viewParams, // additional custom parameters for view
            $data // merging base route parameters last, to avoid broke of integrity
        );
    }

    /**
     * Create a sort url for the field named $title
     * and identified by $key which consists of
     * alias and field. $options holds all link
     * parameters like "alt, class" and so on.
     *
     * $key examples: "article.title" or "['article.title', 'article.subtitle']"
     *
     * @param SlidingPaginationInterface<mixed> $pagination
     * @param string|array<string, mixed>       $title
     * @param string|array<string, mixed>       $key
     * @param array<string, mixed>              $options
     * @param array<string, mixed>              $params
     *
     * @return array<string, mixed>
     */
    public function sortable(SlidingPaginationInterface $pagination, $title, $key, array $options = [], array $params = []): array
    {
        $options = \array_merge([
            'absolute' => UrlGeneratorInterface::ABSOLUTE_PATH,
            'translationParameters' => [],
            'translationDomain' => null,
            'translationCount' => null,
        ], $options);

        $hasFixedDirection = null !== $pagination->getPaginatorOption('sortDirectionParameterName')
            && isset($params[$pagination->getPaginatorOption('sortDirectionParameterName')])
        ;

        $params = \array_merge($pagination->getParams(), $params);

        $direction = $options['defaultDirection'] ?? 'asc';
        if (null !== $pagination->getPaginatorOption('sortDirectionParameterName')) {
            if (isset($params[$pagination->getPaginatorOption('sortDirectionParameterName')])) {
                $direction = $params[$pagination->getPaginatorOption('sortDirectionParameterName')];
            } elseif (isset($options[$pagination->getPaginatorOption('sortDirectionParameterName')])) {
                $direction = $options[$pagination->getPaginatorOption('sortDirectionParameterName')];
            }
        }

        $sorted = $pagination->isSorted($key, $params);

        if ($sorted) {
            if (!$hasFixedDirection) {
                $direction = 'asc' === \strtolower($direction) ? 'desc' : 'asc';
            }

            $class = 'asc' === $direction ? 'desc' : 'asc';
        } else {
            $class = 'sortable';
        }

        if (isset($options['class'])) {
            $options['class'] .= ' '.$class;
        } else {
            $options['class'] = $class;
        }

        if (\is_array($title) && \array_key_exists($direction, $title)) {
            $title = $title[$direction];
        }

        if (\is_array($key)) {
            $key = \implode('+', $key);
        }

        $params = \array_merge(
            $params,
            [
                $pagination->getPaginatorOption('sortFieldParameterName') => $key,
                $pagination->getPaginatorOption('sortDirectionParameterName') => $direction,
                $pagination->getPaginatorOption('pageParameterName') => 1, // reset to 1 on sort
            ]
        );

        $options['href'] = $this->router->generate($pagination->getRoute(), $params, $options['absolute']);

        if (null !== $options['translationDomain']) {
            if (null === $options['translationCount']) {
                $translationParameters = $options['translationParameters'];
            } else {
                $translationParameters = $options['translationParameters'] + ['%count%' => $options['translationCount']];
            }
            $title = $this->translator->trans($title, $translationParameters, $options['translationDomain']);
        }

        if (!isset($options['title'])) {
            $options['title'] = $title;
        }

        unset($options['absolute'], $options['translationParameters'], $options['translationDomain'], $options['translationCount']);

        return \array_merge(
            $pagination->getPaginatorOptions() ?? [],
            $pagination->getCustomParameters() ?? [],
            \compact('options', 'title', 'direction', 'sorted', 'key')
        );
    }

    /**
     * Create a filter url for the field named $title
     * and identified by $key which consists of
     * alias and field. $options holds all link
     * parameters like "alt, class" and so on.
     *
     * $key example: "article.title"
     *
     * @param SlidingPaginationInterface<mixed> $pagination
     * @param array<string, mixed>              $fields
     * @param array<string, mixed>              $options
     * @param array<string, mixed>              $params
     *
     * @return array<string, mixed>
     */
    public function filter(SlidingPaginationInterface $pagination, array $fields, array $options = [], array $params = []): array
    {
        $options = \array_merge([
            'absolute' => UrlGeneratorInterface::ABSOLUTE_PATH,
            'translationParameters' => [],
            'translationDomain' => null,
            'button' => 'Filter',
        ], $options);

        $params = \array_merge($pagination->getParams(), $params);
        $params[$pagination->getPaginatorOption('pageParameterName')] = 1; // reset to 1 on filter

        $filterFieldName = $pagination->getPaginatorOption('filterFieldParameterName');
        $filterValueName = $pagination->getPaginatorOption('filterValueParameterName');

        $selectedField = $params[$filterFieldName] ?? null;
        $selectedValue = $params[$filterValueName] ?? null;

        $action = $this->router->generate($pagination->getRoute(), $params, $options['absolute']);

        foreach ($fields as $field => $title) {
            $fields[$field] = $this->translator->trans($title, $options['translationParameters'], $options['translationDomain']);
        }
        $options['button'] = $this->translator->trans($options['button'], $options['translationParameters'], $options['translationDomain']);

        unset($options['absolute'], $options['translationDomain'], $options['translationParameters']);

        return \array_merge(
            $pagination->getPaginatorOptions() ?? [],
            $pagination->getCustomParameters() ?? [],
            \compact('fields', 'action', 'filterFieldName', 'filterValueName', 'selectedField', 'selectedValue', 'options')
        );
    }
}
