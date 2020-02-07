<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Util\Messenger\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class HandlerFailedExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var string[]|null
     */
    private $exceptions;

    /**
     * @param string[]|null $exceptions
     */
    public function __construct(?array $exceptions = null)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (!$throwable instanceof HandlerFailedException) {
            return;
        }

        if (null === $this->exceptions) {
            $event->setThrowable($throwable->getPrevious());
        }

        foreach ($this->exceptions as $exception) {
            if (is_a($throwable, $exception)) {
                $event->setThrowable($throwable->getPrevious());

                return;
            }
        }
    }
}
