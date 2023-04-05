<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\EventListener;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Contracts\ResponseStackInterface;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Outline;
use Acsiomatic\HttpPayloadBundle\ResponseBody\OutlineStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[AsEventListener]
final readonly class ViewListener
{
    public function __construct(
        private OutlineStack $responseOutlineStack,
        private ResponseStackInterface $responseStack,
        private SerializerInterface $serializer,
        /** @var array<string, int> */
        private array $httpStatusMap = [],
    ) {
    }

    public function __invoke(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $outline = $this->responseOutlineStack->getResponseBodyOutline($request);
        if (!$outline instanceof Outline) {
            return;
        }

        $response = $this->responseStack->getResponse($request);

        $response->headers->set('content-type', $outline->contentType);

        $response->setContent(
            $this->serializer->serialize(
                $event->getControllerResult(),
                $outline->format,
                $outline->serializationContext
            )
        );

        $response->setStatusCode(
            $this->httpStatusMap[$request->getMethod()] ?? match ($request->getMethod()) {
                Request::METHOD_POST => Response::HTTP_CREATED,
                Request::METHOD_DELETE => Response::HTTP_NO_CONTENT,
                default => Response::HTTP_OK,
            }
        );

        $event->setResponse($response);
    }
}
