<?php

// Run from the checkout root after composer install (baseline commit: a91a854):
// git show a91a854:src/Pagination/SlidingPagination.php | sed 's/final class SlidingPagination /final class BenchmarkBaselinePagination /' > /tmp/knp-baseline.php
// php -d opcache.enable_cli=0 -d opcache.file_update_protection=0 tests/Benchmark/SortParameter.php /tmp/knp-baseline.php
// Repeat with -d opcache.enable_cli=1. Both classes load from files for equal OPcache treatment.

require \dirname(__DIR__, 2).'/vendor/autoload.php';

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

if (!isset($argv[1]) || !\is_file($argv[1])) {
    \fwrite(\STDERR, "Pass the baseline file prepared by the command above.\n");
    exit(1);
}

require $argv[1];

function pagination(string $class, array $params, int $range = 5): object
{
    $pagination = new $class($params);
    $pagination->setPaginatorOptions(['sortFieldParameterName' => 'sort']);
    $pagination->setCurrentPageNumber(50);
    $pagination->setTotalItemCount(100000);
    $pagination->setItemNumberPerPage(1);
    $pagination->setPageRange($range);

    return $pagination;
}

function timing(object $pagination, string $method, array $args, int $iterations): float
{
    $start = \hrtime(true);
    for ($i = 0; $i < $iterations; ++$i) {
        $pagination->$method(...$args);
    }

    return (\hrtime(true) - $start) / $iterations;
}

function memory(object $pagination, string $method, array $args): array
{
    \gc_collect_cycles();
    \memory_reset_peak_usage();
    $allocatedBefore = \memory_get_usage(true);
    $before = \memory_get_usage(false);
    for ($i = 0; $i < 1000; ++$i) {
        $pagination->$method(...$args);
    }
    $after = \memory_get_usage(false);
    $peak = \memory_get_peak_usage(false) - $before;
    $allocatedPeak = \memory_get_peak_usage(true) - $allocatedBefore;

    return ['peak_extra_bytes' => $peak, 'retained_bytes' => $after - $before, 'allocator_peak_extra_bytes' => $allocatedPeak];
}

function compare(string $label, object $baseline, object $candidate, string $method, array $args, int $iterations): array
{
    if ($baseline->$method(...$args) !== $candidate->$method(...$args)) {
        throw new RuntimeException('Output mismatch: '.$label);
    }
    for ($i = 0; $i < 1000; ++$i) {
        $baseline->$method(...$args);
        $candidate->$method(...$args);
    }
    $samples = [[], []];
    $objects = [$baseline, $candidate];
    for ($round = 0; $round < 9; ++$round) {
        foreach ($round % 2 ? [1, 0] : [0, 1] as $variant) {
            $samples[$variant][] = \timing($objects[$variant], $method, $args, $iterations);
        }
    }
    $result = ['case' => $label, 'iterations_per_sample' => $iterations];
    foreach (['baseline', 'candidate'] as $variant => $name) {
        $sorted = $samples[$variant];
        \sort($sorted);
        $result[$name] = [
            'median_ns' => $sorted[4],
            'min_ns' => $sorted[0],
            'max_ns' => $sorted[8],
            'samples_ns' => $samples[$variant],
            'memory' => \memory($objects[$variant], $method, $args),
        ];
    }
    $result['time_reduction_percent'] = 100 * (1 - $result['candidate']['median_ns'] / $result['baseline']['median_ns']);

    return $result;
}

$results = [];
foreach ([0, 5, 20, 100, 1000] as $size) {
    $params = ['sort' => 'title'];
    for ($i = 0; $i < $size; ++$i) {
        $params['filter'.$i] = $i;
    }
    foreach ([
        'match, no overrides' => ['title', []],
        'match, override' => ['title', ['sort' => 'title']],
        'mismatch' => ['other', []],
        'null override' => [null, ['sort' => null]],
    ] as $scenario => $args) {
        $results[] = \compare(
            $size.' extra params; '.$scenario,
            \pagination('Knp\\Bundle\\PaginatorBundle\\Pagination\\BenchmarkBaselinePagination', $params),
            \pagination(SlidingPagination::class, $params),
            'isSorted',
            $args,
            100000
        );
    }
}

echo \json_encode([
    'php' => \PHP_VERSION,
    'os' => \php_uname(),
    'opcache_cli' => \ini_get('opcache.enable_cli'),
    'jit' => \ini_get('opcache.jit'),
    'jit_buffer_size' => \ini_get('opcache.jit_buffer_size'),
    'warmup_calls_per_variant' => 1000,
    'rounds' => 9,
    'memory_calls_per_variant' => 1000,
    'results' => $results,
], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR)."\n";
