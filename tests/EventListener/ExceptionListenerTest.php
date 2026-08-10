<?php

namespace Knp\Bundle\PaginatorBundle\Tests\EventListener;

use Knp\Bundle\PaginatorBundle\EventListener\ExceptionListener;
use Knp\Component\Pager\Exception\InvalidValueException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ExceptionListenerTest extends TestCase
{
    public function testLeavesNativeOutOfRangeExceptionUntouched(): void
    {
        $exception = new \OutOfRangeException();
        $event = $this->createEvent($exception);

        (new ExceptionListener())->onKernelException($event);

        self::assertSame($exception, $event->getThrowable());
    }

    public function testConvertsInvalidPaginatorValueToNotFoundException(): void
    {
        $exception = new InvalidValueException();
        $event = $this->createEvent($exception);

        (new ExceptionListener())->onKernelException($event);

        self::assertInstanceOf(NotFoundHttpException::class, $event->getThrowable());
        self::assertSame($exception, $event->getThrowable()->getPrevious());
    }

    private function createEvent(\Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception,
        );
    }
}
