<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Dto;

use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final class Person
{
    #[Assert\Length(min: 3)]
    public string $name;

    #[Assert\LessThan('today')]
    #[Serializer\Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public \DateTime $birthdate;

    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\GreaterThanOrEqual(20, groups: ['strict'])]
    public int|null $height = null;
}
