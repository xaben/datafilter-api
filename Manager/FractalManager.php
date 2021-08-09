<?php

namespace Soluti\DataFilterBundle\Manager;

use League\Fractal\Manager;
use Symfony\Component\HttpFoundation\RequestStack;

class FractalManager
{
    protected Manager $fractal;

    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, ?Manager $manager = null)
    {
        if (empty($manager)) {
            $manager = new Manager();
        }

        $this->fractal = $manager;
        $this->requestStack = $requestStack;
    }

    public function createData($resource)
    {
        $this->parseQueryParams();

        return $this->fractal->createData($resource);
    }

    private function parseQueryParams()
    {
        $this->fractal->parseIncludes(
            $this->requestStack->getCurrentRequest()->get('include', '')
        );
        $this->fractal->parseExcludes(
            $this->requestStack->getCurrentRequest()->get('exclude', '')
        );
    }
}
