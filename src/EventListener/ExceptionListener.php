<?php

namespace Knp\Bundle\PaginatorBundle\EventListener;

use OutOfRangeException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Intercept OutOfRangeException and throw http-related exceptions instead.
 */
final class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof OutOfRangeException) {
            $event->setThrowable(new NotFoundHttpException('Not Found.', $exception));
        }
    }
}
