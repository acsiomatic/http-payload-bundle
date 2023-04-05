<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody;

use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class OutlineStack
{
    /**
     * @var \SplObjectStorage<Request, Outline>
     */
    private \SplObjectStorage $outlines;

    public function __construct()
    {
        $this->outlines = new \SplObjectStorage();
    }

    public function addResponseBodyOutline(Request $request, Outline $outline): void
    {
        $this->outlines[$request] = $outline;
    }

    public function getResponseBodyOutline(Request $request): Outline|null
    {
        return $this->outlines[$request] ?? null;
    }
}
