<?php

namespace Acsiomatic\HttpPayloadBundle\Tests;

trait ResourceTrait
{
    public function resourcePath(string $filename): string
    {
        return __DIR__.'/Fixture/Resource/'.$filename;
    }

    public function resourceContent(string $filename): string
    {
        return (string) file_get_contents($this->resourcePath($filename));
    }
}
