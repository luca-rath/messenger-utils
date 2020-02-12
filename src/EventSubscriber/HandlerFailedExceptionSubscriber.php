<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Util\Messenger\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

class HandlerFailedExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var string|null
     */
    private $busName;

    /**
     * @var string[]|null
     */
    private $exceptions;

    /**
     * @param string[]|null $exceptions
     */
    public function __construct(?string $busName = null, ?array $exceptions = null)
    {
        $this->busName = $busName;
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

        if (null === ($previous = $throwable->getPrevious())) {
            return;
        }

        if (null !== $this->busName) {
            $busNameStamp = $throwable->getEnvelope()->last(BusNameStamp::class);

            if (null === $busNameStamp || $busNameStamp->getBusName() !== $this->busName) {
                return;
            }
        }

        if (null === $this->exceptions) {
            $event->setThrowable($previous);

            return;
        }

        foreach ($this->exceptions as $exception) {
            if (is_a($previous, $exception)) {
                $event->setThrowable($previous);

                return;
            }
        }
    }
}
