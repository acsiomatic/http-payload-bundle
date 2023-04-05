<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\EventListener;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Contracts\ResponseStackInterface;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Outline;
use Acsiomatic\HttpPayloadBundle\ResponseBody\OutlineStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[AsEventListener]
final readonly class ExceptionListener
{
    public function __construct(
        private OutlineStack $outlineStack,
        private ResponseStackInterface $responseStack,
        private SerializerInterface $serializer,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $outline = $this->outlineStack->getResponseBodyOutline($request);
        if (!$outline instanceof Outline) {
            // @todo honor RouteAttribute in case of not acceptable exception
            return;
        }

        $throwable = $event->getThrowable();

        $response = $this->responseStack->getResponse($request);

        $response->headers->add(array_merge(
            ['content-type' => $outline->contentType],
            $throwable instanceof HttpExceptionInterface ? $throwable->getHeaders() : [],
        ));

        $response->setContent(
            $this->serializer->serialize(
                $throwable,
                $outline->format,
                $outline->serializationContext
            )
        );

        $response->setStatusCode(
            $throwable instanceof HttpExceptionInterface
                ? $throwable->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR
        );

        $event->setResponse($response);
    }
}
