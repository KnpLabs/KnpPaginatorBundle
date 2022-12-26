<?php

namespace Knp\Bundle\PaginatorBundle\EventListener;

use OutOfRangeException;
use Knp\Component\Pager\Exception\InvalidValueException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;
use function preg_match;

/**
 * Intercept OutOfRangeException/UnexpectedValueException and throw http-related exceptions instead.
 */
final class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof OutOfRangeException) {
            $event->setThrowable(new NotFoundHttpException('Not Found.', $exception));
        } elseif ($exception instanceof InvalidValueException) {
            $event->setThrowable(new NotFoundHttpException('Not Found.', $exception));
        } elseif ($exception instanceof UnexpectedValueException && self::isInternalException($exception)) {
            $event->setThrowable(new NotFoundHttpException('Not Found', $exception));
        }
    }

    private static function isInternalException(UnexpectedValueException $exception): bool
    {
        $messages = [
            '/^Cannot filter by\: \[.+\] this field is not in allow list$/',
            '/^Cannot sort by\: \[.+\] this field is not in allow list\.$/',
            '/^Cannot sort with array parameter$/',
            '/^ODM query must be a FIND type query$/',
            '/^There is no component aliased by \[.+\] in the given Query$/',
            '/^There is no component field \[.+\] in the given Query$/',
            '/^There is no such field \[.+\] in the given Query component, aliased by \[.+\]$/',
        ];
        foreach ($messages as $regex) {
            if (preg_match($regex, $exception->getMessage()) > 0) {
                return true;
            }
        }

        return false;
    }
}
