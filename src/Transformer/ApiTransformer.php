<?php

declare(strict_types=1);

namespace Xaben\DataFilterApi\Transformer;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\TransformerAbstract;
use Xaben\DataFilter\Transformer\Transformer;

abstract class ApiTransformer extends TransformerAbstract implements Transformer
{
    protected Manager $fractalManager;

    public function __construct(Manager $fractalManager)
    {
        $this->fractalManager = $fractalManager;
    }

    public function transformCollection(iterable $data): array
    {
        return $this->fractalManager->createData(new FractalCollection($data, $this))->toArray();
    }

    abstract public function transform(mixed $data): array;
}
