<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\Contracts;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponseStackInterface
{
    /**
     * @param Request|null $request If null, it assumes the current request as value
     */
    public function getResponse(Request $request = null): Response;
}
