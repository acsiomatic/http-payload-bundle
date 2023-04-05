<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\ResponseBody;

use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class ResponseTweakTest extends TestCase
{
    public function testControllerCanTweakResponse(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);

        $client->request('GET', '/response-body/tweak-response');
        $response = $client->getResponse();

        self::assertTrue($response->isSuccessful());
        self::assertEquals('bar', $response->headers->get('x-foo'));
    }

    public function testControllerCanTweakResponseUnderException(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);

        $client->request('GET', '/response-body/tweak-response-and-throw-exception');
        $response = $client->getResponse();

        self::assertFalse($response->isSuccessful());
        self::assertEquals('qux', $response->headers->get('x-baz'));
    }
}
