<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\EventListener;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Attribute\ResponseBody;
use Acsiomatic\HttpPayloadBundle\ResponseBody\OutlineResolver;
use Acsiomatic\HttpPayloadBundle\ResponseBody\OutlineStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * @internal
 */
#[AsEventListener(event: ControllerEvent::class, method: 'onController')]
final readonly class ControllerListener
{
    public function __construct(
        private OutlineResolver $outlineResolver,
        private OutlineStack $outlineStack,
    ) {
    }

    public function onController(ControllerEvent $event): void
    {
        $attribute = $this->extractResponseBodyAttribute($event);
        if (!$attribute instanceof ResponseBody) {
            return;
        }

        $request = $event->getRequest();
        $outline = $this->outlineResolver->resolve($request, $attribute);
        $this->outlineStack->addResponseBodyOutline($request, $outline);
    }

    private function extractResponseBodyAttribute(ControllerEvent $event): ResponseBody|null
    {
        return array_reduce(
            array_filter(
                $event->getAttributes()[ResponseBody::class] ?? [],
                static fn ($attribute): bool => $attribute instanceof ResponseBody
            ),
            static fn (
                ResponseBody|null $carry,
                ResponseBody $item,
            ): ResponseBody => new ResponseBody(
                $item->preset ?? $carry?->preset,
                $item->formats ?? $carry?->formats,
                $item->serializationContext ?? $carry?->formats,
            )
        );
    }
}
