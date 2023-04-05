<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Contracts\ResponseStackInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class ResponseStack implements ResponseStackInterface
{
    /** @var \SplObjectStorage<Request, Response> */
    private \SplObjectStorage $responses;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        $this->responses = new \SplObjectStorage();
    }

    public function getResponse(Request $request = null): Response
    {
        $request ??= $this->requestStack->getCurrentRequest();

        return $this->responses[$request] ??= new Response();
    }
}
