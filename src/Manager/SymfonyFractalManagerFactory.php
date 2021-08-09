<?php

declare(strict_types=1);

namespace Xaben\DataFilterApi\Manager;

use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SymfonyFractalManagerFactory
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(): Manager
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());

        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest instanceof Request) {
            $manager->parseIncludes(
                $currentRequest->get('include', '')
            );

            $manager->parseExcludes(
                $currentRequest->get('exclude', '')
            );
        }

        return $manager;
    }
}
