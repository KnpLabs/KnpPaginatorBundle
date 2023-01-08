<?php

namespace Knp\Bundle\PaginatorBundle\EventListener;

use Knp\Component\Pager\Exception\InvalidValueException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Intercept OutOfRangeException/UnexpectedValueException and throw http-related exceptions instead.
 */
final class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof \OutOfRangeException || $exception instanceof InvalidValueException) {
            $event->setThrowable(new NotFoundHttpException('Not Found.', $exception));
        }
    }
}
